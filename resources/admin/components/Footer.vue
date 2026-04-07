<template>
	<footer class="border-t border-slate-200/80 bg-white/70 px-5 py-4 text-sm text-slate-500 sm:px-8 lg:px-10">
		<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
			<p>Cloudy Learning admin workspace for lessons, users, and content operations.</p>
			<p>Last synced {{ todayLabel }}</p>
		</div>
	</footer>
</template>

<script setup lang="ts">
defineProps<{
	todayLabel: string;
}>();
</script>
