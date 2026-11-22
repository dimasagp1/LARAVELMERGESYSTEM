<script setup>
import QualityOverview from './Overview.vue';

defineProps({
  module: { type: Object, required: true },
  kpis: { type: Object, default: () => ({}) },
  deviationsBySeverity: { type: Object, default: () => ({}) },
  capaStatus: { type: Object, default: () => ({}) },
  docsByLevel: { type: Object, default: () => ({}) },
  fishboneCounts: { type: Object, default: () => ({}) },
  pendingApprovals: { type: Array, default: () => [] },
  recentActivities: { type: Array, default: () => [] },
});
</script>

<template>
  <QualityOverview v-bind="$props" />
</template>
