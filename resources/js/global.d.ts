import type { route as ziggyRoute } from 'ziggy-js';

declare global {
    const route: typeof ziggyRoute;
}

export {};

declare module 'markdown-it' {
    const MarkdownIt: any;
    export default MarkdownIt;
}

declare module 'highlight.js' {
    const hljs: any;
    export default hljs;
    export function getLanguage(name: string): any;
    export function highlight(code: string, options: { language: string; ignoreIllegals?: boolean }): { value: string };
}
