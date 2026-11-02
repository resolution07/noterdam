export interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    settings?: Record<string, unknown>;
    created_at?: string;
    updated_at?: string;
}

export interface Project {
    id: number;
    owner_id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string | null;
    is_archived: boolean;
    created_at: string;
    updated_at: string;
    owner?: User;
    members?: ProjectMember[];
    notes_count?: number;
    deleted_at?: string | null;
}

export interface ProjectMember {
    id: number;
    name: string;
    email: string;
    pivot: {
        role: 'owner' | 'editor' | 'viewer';
    };
}

export interface Tag {
    id: number;
    name: string;
    color: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface Attachment {
    id: number;
    note_id: number;
    uploaded_by: number | null;
    original_name: string;
    storage_path: string;
    mime_type: string;
    size: number;
    checksum_sha256: string | null;
    created_at: string;
    uploader?: User | null;
}

export interface Note {
    id: number;
    project_id: number;
    author_id: number | null;
    title: string;
    content: string;
    content_html?: string | null;
    is_pinned: boolean;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    author?: User | null;
    tags?: Tag[];
    attachments?: Attachment[];
}

export interface Invite {
    id: number;
    code: string;
    email: string | null;
    created_by: number;
    used_at: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
    creator?: User;
}

export interface Token {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
}

export interface Flash {
    success?: string | null;
    error?: string | null;
}

export interface ApiResponse<T> {
    data: T;
}
