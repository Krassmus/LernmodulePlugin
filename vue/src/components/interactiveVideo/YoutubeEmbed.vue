<script lang="ts">
import { defineComponent } from 'vue';
import { regex } from '@/components/interactiveVideo/extractYoutubeUrl';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'YoutubeEmbed',
  props: {
    url: {
      type: String,
      required: true,
    },
  },
  computed: {
    videoId() {
      return this.extractId(this.url);
    },
    embedUrl() {
      // TODO implement 'start at' time using 'start=' parameter
      return `https://www.youtube-nocookie.com/embed/${this.videoId}?start=0`;
    },
  },
  methods: {
    $gettext,
    extractId(youtubeUrl: string): string | undefined {
      const match = youtubeUrl.match(regex);
      return match?.[1];
    },
  },
});
</script>

<template>
  <div v-if="!videoId">
    {{
      $gettext('Das eingegebene URL, %{ url }%, ist nicht gültig.', {
        url: url,
      })
    }}
  </div>
  <iframe
    v-else
    width="560"
    height="315"
    :src="embedUrl"
    title="YouTube video player"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    allowfullscreen
  ></iframe>
</template>

<style scoped></style>
