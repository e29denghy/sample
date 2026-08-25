<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleManager;
use App\Services\MarkdownRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Articles/Index', [
            'articles' => Article::with(['draftRevision', 'publishedRevision'])->latest('updated_at')->paginate(20),
            'createUrl' => route('admin.articles.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Articles/Form', $this->formProps(new Article, null));
    }

    public function store(Request $request, ArticleManager $manager): RedirectResponse
    {
        $article = $manager->saveDraft(null, $this->validated($request), $request->user());

        return redirect()->route('admin.articles.edit', $article)->with('success', '草稿已保存。');
    }

    public function edit(Article $article): Response
    {
        $article->load(['draftRevision', 'publishedRevision', 'tags']);

        return Inertia::render('Admin/Articles/Form', $this->formProps($article, $article->draftRevision ?: $article->publishedRevision));
    }

    public function update(Request $request, Article $article, ArticleManager $manager): RedirectResponse
    {
        $article = $manager->saveDraft($article, $this->validated($request, $article), $request->user());

        return redirect()->route('admin.articles.edit', $article)->with('success', '新修订草稿已保存，尚未影响公开正文。');
    }

    public function preview(Request $request, MarkdownRenderer $renderer): Response
    {
        $data = $this->validated($request);

        return Inertia::render('Admin/Articles/Preview', [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'html' => $renderer->render($data['markdown']),
        ]);
    }

    public function publish(Request $request, Article $article, ArticleManager $manager): RedirectResponse
    {
        $manager->publish($article, $request->user());

        return back()->with('success', '文章已发布，网站、RSS 和只读 API 将读取这一修订。');
    }

    public function archive(Request $request, Article $article, ArticleManager $manager): RedirectResponse
    {
        $manager->archive($article, $request->user());

        return redirect()->route('admin.articles.index')->with('success', '文章已归档。');
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($article)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'markdown' => ['required', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'verification_status' => ['required', 'in:valid,review_required,outdated'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:80'],
        ]);
        $data['tags'] = collect($data['tags'] ?? [])
            ->flatMap(fn (string $tag) => preg_split('/[,，]/u', $tag) ?: [])
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->values()
            ->all();

        return $data;
    }

    private function formProps(Article $article, $revision): array
    {
        return [
            'article' => $article->exists ? $article : null,
            'revision' => $revision,
            'formAction' => $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store'),
            'formMethod' => $article->exists ? 'patch' : 'post',
            'previewAction' => route('admin.articles.preview'),
            'publishAction' => $article->exists && $article->draftRevision ? route('admin.articles.publish', $article) : null,
            'archiveAction' => $article->exists ? route('admin.articles.archive', $article) : null,
            'publicUrl' => $article->exists && $article->isPublic() ? route('articles.show', $article->slug) : null,
            'mediaUploadAction' => route('admin.media.store'),
        ];
    }
}
