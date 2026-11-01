import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { Flash, PageProps } from '@/types';

export function useFlash() {
    const page = usePage<PageProps>();

    watch(
        () => page.props.flash as Flash,
        (flash) => {
            if (flash?.success) toast.success(flash.success);
            if (flash?.error) toast.error(flash.error);
        },
        { immediate: true, deep: true },
    );
}
