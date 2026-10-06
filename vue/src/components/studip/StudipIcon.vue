<template>
  <label v-if="name" class="icon-button undecorated">
    <input type="submit" hidden v-bind="attrs" />
    <IconContent v-bind="iconAttrs" />
    <span v-if="text">{{ text }}</span>
  </label>

  <IconContent v-else v-bind="allAttrs" />
</template>

<script lang="ts" setup>
import {
  computed,
  defineComponent,
  h,
  ref,
  useAttrs,
  watch,
  defineProps,
  withDefaults,
  CSSProperties,
  PropType,
} from 'vue';
import iconLoader, { CachedIcon } from '@/components/studip/icon-loader';

interface IconProps {
  ariaRole?: string;
  name?: string;
  role?: string;
  shape: string;
  size?: number | null;
  inline?: boolean;
  text?: string;
}

const props = withDefaults(defineProps<IconProps>(), {
  role: 'clickable',
  size: null,
  inline: false,
});

const isSvg = ref(false);
const content = ref('');

const attrs = useAttrs();
const iconAttrs = computed(() => ({
  ariaRole: props.ariaRole,
  class: cssClass.value,
  content: content.value,
  isSvg: isSvg.value,
  style: computedStyle.value,
}));
const allAttrs = computed(() => ({
  ...attrs,
  ...iconAttrs.value,
}));

const cssClass = computed(() => [
  'studip-icon',
  props.inline ? 'studip-icon-inline' : '',
  `icon-role-${props.role}`,
  `icon-shape-${shapeName.value}`,
]);

const computedStyle = computed(() =>
  props.size ? { width: `${props.size}px`, height: `${props.size}px` } : {}
);

const shapeName = computed((): string => {
  const containsUrl = (shape: string): boolean =>
    /\bhttps?:\/\/\S+/i.test(shape);
  return containsUrl(props.shape)
    ? props.shape.split('/').pop()?.replace('.svg', '') ?? ''
    : props.shape;
});

watch(
  () => props.shape,
  async (shape) => {
    const icon: CachedIcon = await iconLoader.load(shape);
    isSvg.value = icon.isSvg;
    content.value = icon.content;
  },
  { immediate: true }
);

const IconContent = defineComponent({
  props: {
    isSvg: Boolean,
    content: String,
    ariaRole: String,
    cssClass: String,
    computedStyle: Object as PropType<CSSProperties>,
  },
  setup(props, { attrs }) {
    const baseAttrs = {
      class: props.cssClass,
      role: props.ariaRole,
      style: props.computedStyle,
      ...attrs,
    };

    return () =>
      props.isSvg
        ? h('div', {
            innerHTML: props.content,
            ...baseAttrs,
          })
        : h('img', {
            src: props.content,
            ...baseAttrs,
          });
  },
});
</script>
