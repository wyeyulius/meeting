<script lang="ts" setup>
import { type HTMLAttributes, computed } from 'vue'
import { RangeCalendarPrev, type RangeCalendarPrevProps, useForwardProps } from 'radix-vue'
import { ChevronLeftIcon } from '@radix-icons/vue'
import { cn } from '@/lib/utils'
import { buttonVariants } from '@/Components/ui/button'

const props = defineProps<RangeCalendarPrevProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <RangeCalendarPrev
    :class="cn(
      buttonVariants({ variant: 'outline' }),
      'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
      props.class,
    )"
    v-bind="forwardedProps"
  >
    <slot>
      <ChevronLeftIcon class="h-4 w-4" />
    </slot>
  </RangeCalendarPrev>
</template>
