<script setup lang="ts">
import { cn } from "@/lib/utils";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
</script>

<template>
    <nav
        :class="
            cn('flex items-center space-x-4 lg:space-x-6', $attrs.class ?? '')
        "
    >
        <Link
            :href="route('dashboard')"
            class="text-sm font-medium transition-colors hover:text-primary"
            :class="{ 'text-muted-foreground': !route().current('dashboard') }"
        >
            Dashboard
        </Link>
        <Link
            :href="route('zoom.index')"
            class="text-sm font-medium transition-colors hover:text-primary"
            :class="{ 'text-muted-foreground': !route().current('zoom.index') }"
        >
            Pengajuan
        </Link>
        <!-- <a
            href="/examples/dashboard"
            class="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
        >
            Sign In
        </a> -->
        <!-- <a
      href="/examples/dashboard"
      class="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
    >
      Settings
    </a> -->
    </nav>
</template>
