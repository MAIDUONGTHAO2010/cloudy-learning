<template>
    <div class="relative" ref="dropdownRef">
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-400 transition hover:bg-white/10 hover:text-white"
        >
            <span>{{ currentFlag }}</span>
            <span class="hidden sm:inline">{{ currentLabel }}</span>
            <svg class="h-3 w-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-11 z-50 min-w-[140px] rounded-2xl border border-white/10 bg-slate-900 py-1 shadow-xl"
        >
            <button
                v-for="loc in locales"
                :key="loc.code"
                type="button"
                @click="select(loc.code)"
                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm transition hover:bg-white/5"
                :class="locale === loc.code ? 'text-sky-400 font-semibold' : 'text-slate-300'"
            >
                <span>{{ loc.flag }}</span>
                <span>{{ loc.label }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { setLocale, type SupportedLocale } from '../../i18n';

const { locale } = useI18n();
const open = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const locales = [
    { code: 'vi' as SupportedLocale, label: 'Tiếng Việt', flag: '🇻🇳' },
    { code: 'en' as SupportedLocale, label: 'English',    flag: '🇬🇧' },
    { code: 'ja' as SupportedLocale, label: '日本語',      flag: '🇯🇵' },
];

const currentFlag = computed(() => locales.find((l) => l.code === locale.value)?.flag ?? '🌐');
const currentLabel = computed(() => locales.find((l) => l.code === locale.value)?.label ?? locale.value);

const select = (code: SupportedLocale) => {
    setLocale(code);
    open.value = false;
};

const onClickOutside = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        open.value = false;
    }
};
onMounted(() => document.addEventListener('click', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));
</script>
