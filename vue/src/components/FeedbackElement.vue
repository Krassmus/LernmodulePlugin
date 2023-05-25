<template>
  <div class="h5pFeedbackContainer">
    <div class="h5pFeedbackContainerTop">
      <div v-if="message" class="h5pFeedbackText">
        {{ message }}
      </div>
      <meter id="score" min="0" :max="maxPoints" :value="achievedPoints" />
    </div>
  </div>
</template>

<script lang="ts">
// need v-model to provide and get content -> <studip-wysiwyg v-model="content" />
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'feedback-element',
  props: {
    message: String,
    maxPoints: Number,
    achievedPoints: Number,
  },
  data() {
    return {};
  },
  mounted() {},
  methods: {},
});
</script>

<style></style>
