export type Permission = {
    id: string;
    name: string;
    slug: string;
};

export type Role = {
    id: string;
    name: string;
    slug: string;
    description?: string | null;
    permissions?: Permission[];
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Role[];
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    permissions: string[];
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
