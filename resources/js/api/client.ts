import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const TOKEN_KEY = 'noterdam-token';

function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
}

const client = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

client.interceptors.request.use((config) => {
    const token = getToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

client.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const auth = useAuthStore();
            auth.clearAuth();
            window.location.href = '/login';
        }
        return Promise.reject(error);
    },
);

export function setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
}

export function removeToken(): void {
    localStorage.removeItem(TOKEN_KEY);
}

export { TOKEN_KEY };
export default client;
