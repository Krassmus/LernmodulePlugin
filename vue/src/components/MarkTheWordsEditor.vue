<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- TODO refrain from mutating taskDefinition directly -- it breaks undo/redo-->
<!-- eslint-disable vue/no-mutating-props -->
<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Mark The Words') }}</legend>
      <div class="h5pEditorTopPanel">
        <button
          @click="addSolution"
          class="button"
          type="button"
          style="margin-right: 0.1em"
        >
          {{ $gettext('Richtiges Wort markieren') }}
        </button>
        <div class="tooltip tooltip-icon" :data-tooltip="instructions" />
      </div>
      <StudipWysiwyg
        v-model="taskDefinition.template"
        ref="wysiwyg"
        force-soft-breaks
        remove-wrapping-p-tag
        disable-autoformat
      />
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Feedback') }}</legend>
      <label>
        {{
          $gettext(
            'Ergebnismitteilung (mögliche Variablen :correct und :total):'
          )
        }}
        <input
          type="text"
          v-model="taskDefinition.strings.resultMessage"
          style="width: 100%"
        />
      </label>
      <label>{{
        $gettext('Benutzerdefiniertes Feedback für beliebige Punktebereiche:')
      }}</label>
      <div class="feedbackContainer">
        <div class="feedbackPercentagesChild">
          <label>
            {{ $gettext('Prozent') }}
          </label>
          <template v-for="(feedback, i) in taskDefinition.feedback" :key="i">
            <input
              type="number"
              min="0"
              max="100"
              v-model="taskDefinition.feedback[i].percentage"
            />
          </template>
        </div>

        <div class="feedbackMessagesChild">
          <label>
            {{ $gettext('Mitteilung') }}
          </label>
          <div
            class="feedbackMessagesChildSubdivision"
            v-for="(feedback, i) in taskDefinition.feedback"
            :key="i"
          >
            <input type="text" v-model="taskDefinition.feedback[i].message" />
            <button
              type="button"
              :title="titleForDeleteButtonForFeedback(feedback)"
              @click="removeFeedback(feedback)"
            >
              <img :src="urlForIcon('trash')" width="16" height="16" />
            </button>
          </div>
        </div>
      </div>

      <button type="button" class="button" @click="addFeedback">
        {{ $gettext('Neuer Bereich') }}
      </button>
    </fieldset>
  </form>
</template>

<script lang="ts">
// Allow us to mutate the prop 'taskDefinition' as much as we want
// TODO refrain from mutating taskDefinition directly -- it breaks undo/redo
/* eslint-disable vue/no-mutating-props */
import { defineComponent, PropType } from 'vue';
import { Feedback, MarkTheWordsTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';

export default defineComponent({
  name: 'MarkTheWordsEditor',
  components: { StudipWysiwyg },
  props: {
    taskDefinition: {
      type: Object as PropType<MarkTheWordsTask>,
      required: true,
    },
  },
  methods: {
    $gettext,

    /**
     * Surround the selected text with two asterisks
     */
    addSolution() {
      const wysiwygEl = (this.$refs.wysiwyg as any)?.$el;
      const editor = window.STUDIP.wysiwyg.getEditor(wysiwygEl);
      if (!editor) {
        console.error('getEditor(wysiwygEl) returned: ', editor);
        throw new Error('Could not get reference to wysiwyg editor');
      }
      const selection = editor.model.document.selection;
      const start = selection.getFirstPosition();
      const end = selection.getLastPosition();
      if (!start || !end) {
        console.error('selection start: ', start, ' selection end: ', end);
        throw new Error('Could not get selection in editor');
      }
      editor.model.change((writer) => {
        writer.insertText('*', end);
        writer.insertText('*', start);
      });
    },

    titleForDeleteButtonForFeedback(feedback: Feedback): string {
      return this.$gettext(
        'Entferne den Feedback-Bereich, der ab %{ percentage }% anfängt.',
        { percentage: feedback.percentage.toString() }
      );
    },

    addFeedback(): void {
      this.taskDefinition.feedback.push({
        percentage: this.feedbackSortedByScore[0]?.percentage,
        message: 'Feedback',
      });
    },

    removeFeedback(feedbackToRemove: Feedback): void {
      this.taskDefinition.feedback = this.taskDefinition.feedback.filter(
        (feedback) => feedback !== feedbackToRemove
      );
    },

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },

  computed: {
    instructions(): string {
      return $gettext(
        'Markieren Sie ein Wort als Lösung, indem Sie ein Sternchen (*) vor und hinter dem Wort setzen oder markieren Sie ein Wort und klicken Sie den "Richtiges Wort markieren"–Button.'
      );
    },

    feedbackSortedByScore(): Feedback[] {
      return this.taskDefinition.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
    },
  },
});
</script>

<style scoped>
.feedbackContainer {
  display: flex;
  gap: 0.5em;
  justify-content: flex-start;
  align-items: center;
  max-width: 48em;
}

.feedbackPercentagesChild {
  flex: 0 100px;
  display: flex;
  flex-direction: column;
}

.feedbackMessagesChild {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.feedbackMessagesChildSubdivision {
  display: flex;
  align-items: center;
  gap: 0.5em;
}
</style>
