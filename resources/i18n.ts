import { createI18n } from 'vue-i18n';
import vi from './locales/vi';
import en from './locales/en';
import ja from './locales/ja';

const STORAGE_KEY = 'cloudy-learning.locale';

export const supportedLocales = ['vi', 'en', 'ja'] as const;
export type SupportedLocale = typeof supportedLocales[number];

const savedLocale = (typeof window !== 'undefined' && localStorage.getItem(STORAGE_KEY)) as SupportedLocale | null;
const defaultLocale: SupportedLocale = savedLocale && supportedLocales.includes(savedLocale) ? savedLocale : 'vi';

export const i18n = createI18n({
    legacy: false,
    locale: defaultLocale,
    fallbackLocale: 'en',
    messages: { vi, en, ja },
});

export const setLocale = (locale: SupportedLocale) => {
    (i18n.global.locale as any).value = locale;
    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, locale);
        document.documentElement.lang = locale;
    }
};
