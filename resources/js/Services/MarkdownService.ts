import MarkdownIt from 'markdown-it';
import hljs from 'highlight.js';

const md = new MarkdownIt({
    html: false,
    linkify: true,
    typographer: true,
    highlight: (str, lang) => {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return `<pre class="hljs"><code>${hljs.highlight(str, { language: lang, ignoreIllegals: true }).value}</code></pre>`;
            } catch {
                // ignore
            }
        }
        return `<pre><code>${md.utils.escapeHtml(str)}</code></pre>`;
    },
});

export const MarkdownService = {
    render(content: string): string {
        return md.render(content || '');
    },
};
