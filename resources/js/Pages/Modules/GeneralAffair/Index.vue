<script setup>
import GaOverview from './Overview.vue';

defineProps({
  module: { type: Object, required: true },
  kpis: { type: Object, default: () => ({}) },
  woStatusCounts: { type: Object, default: () => ({}) },
  woPriorityCounts: { type: Object, default: () => ({}) },
  assetsByPlant: { type: Array, default: () => [] },
  patrolSummary: { type: Object, default: () => ({}) },
  pendingApprovals: { type: Array, default: () => [] },
  recentActivities: { type: Array, default: () => [] },
});
</script>

<template>
  <GaOverview v-bind="$props" />
</template>
