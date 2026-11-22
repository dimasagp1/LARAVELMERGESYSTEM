import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useDeveloperModeStore = defineStore('developerMode', () => {
    const isEnabled = ref(false);
    const inspectedElement = ref(null);
    const queryLogs = ref([]);

    function toggle() {
        isEnabled.value = !isEnabled.value;
    }

    function setInspected(data) {
        inspectedElement.value = data;
    }

    function clearInspected() {
        inspectedElement.value = null;
    }

    return {
        isEnabled,
        inspectedElement,
        queryLogs,
        toggle,
        setInspected,
        clearInspected,
    };
});
