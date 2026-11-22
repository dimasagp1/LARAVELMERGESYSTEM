import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    // Check initial dark mode from localStorage or system preference
    const savedTheme = typeof window !== 'undefined' ? localStorage.getItem('herbatech_theme') : null;
    const isDark = ref(savedTheme === 'dark');

    if (typeof window !== 'undefined') {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function toggleTheme() {
        isDark.value = !isDark.value;
        if (typeof window !== 'undefined') {
            if (isDark.value) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('herbatech_theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('herbatech_theme', 'light');
            }
        }
    }

    return {
        isDark,
        toggleTheme,
    };
});
