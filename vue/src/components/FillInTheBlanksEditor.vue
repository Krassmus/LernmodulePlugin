<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Fill in the Blanks') }}</legend>
      <div class="h5pEditorTopPanel">
        <button
          @click="addBlank"
          class="button"
          type="button"
          style="margin-right: 0.1em"
        >
          {{ $gettext('Lücke hinzufügen') }}
        </button>
        <div class="tooltip tooltip-icon" :data-tooltip="instructions" />
      </div>
      <studip-wysiwyg v-model="taskDefinition.template" id="ckeditorElement" />
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label>
        <input type="checkbox" v-model="taskDefinition.caseSensitive" />
        {{ $gettext('Groß- und Kleinschreibung beachten') }}
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.acceptTypos" />
        {{ $gettext('Rechtschreib- oder Tippfehler ignorieren') }}
      </label>

      <label>
        {{ $gettext('Korrigiert wird') }}
        <select v-model="taskDefinition.autoCorrect">
          <option :value="false">
            {{ $gettext('manuell per Button') }}
          </option>
          <option :value="true">
            {{ $gettext('automatisch nach Eingabe') }}
          </option>
        </select>
      </label>

      <label :class="taskDefinition.autoCorrect ? 'setting-disabled' : ''">
        {{ $gettext('Text im Button:') }}
        <input
          type="text"
          :disabled="taskDefinition.autoCorrect"
          v-model="taskDefinition.strings.checkButton"
        />
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.retryAllowed" />
        {{ $gettext('Mehrere Versuche erlauben') }}
      </label>
      <label :class="taskDefinition.retryAllowed ? '' : 'setting-disabled'"
        >{{ $gettext('Text im Button:') }}

        <input
          type="text"
          :disabled="!taskDefinition.retryAllowed"
          v-model="taskDefinition.strings.retryButton"
        />
      </label>

      <label>
        <input
          type="checkbox"
          v-model="taskDefinition.showSolutionsAllowed"
          @change="
            !taskDefinition.showSolutionsAllowed
              ? (taskDefinition.allBlanksMustBeFilledForSolutions =
                  taskDefinition.showSolutionsAllowed)
              : ''
          "
        />
        {{ $gettext('Lösungen können angezeigt werden') }}
      </label>
      <label
        :class="taskDefinition.showSolutionsAllowed ? '' : 'setting-disabled'"
      >
        {{ $gettext('Text im Button:') }}
        <input
          type="text"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.strings.solutionsButton"
        />
      </label>
      <label
        :class="taskDefinition.showSolutionsAllowed ? '' : 'setting-disabled'"
      >
        <input
          type="checkbox"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.allBlanksMustBeFilledForSolutions"
        />
        {{
          $gettext(
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.'
          )
        }}
      </label>
      <label
        :class="
          taskDefinition.allBlanksMustBeFilledForSolutions
            ? ''
            : 'setting-disabled'
        "
      >
        {{ $gettext('Mitteilung, wenn nicht alle Lücken ausgefüllt sind:') }}
        <input
          type="text"
          :disabled="!taskDefinition.allBlanksMustBeFilledForSolutions"
          v-model="taskDefinition.strings.fillInAllBlanksMessage"
          style="width: 100%"
        />
      </label>
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
import { defineComponent } from 'vue';
import { Feedback, FillInTheBlanksTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  components: { StudipWysiwyg },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as FillInTheBlanksTask,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],

    feedbackSortedByScore(): Feedback[] {
      return this.taskDefinition.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
    },
    instructions(): string {
      return $gettext(
        'Fügen Sie Lücken hinzu, indem Sie ein Sternchen (*) vor und hinter dem korrekten Wort bzw. den Wörtern setzen oder markieren Sie ein Wort und klicken Sie den "Lücke hinzufügen"–Button.' +
          ' Sie können alternative Antworten mit einem Schrägstrich (/) hinzufügen.' +
          ' Außerdem können Sie einen Tooltip mit einem Doppelpunkt (:) hinzufügen.'
      );
    },
  },
  methods: {
    $gettext,
    titleForDeleteButtonForFeedback(feedback: Feedback): string {
      return this.$gettext(
        'Entferne den Feedback-Bereich, der ab %{ percentage }% anfängt.',
        { percentage: feedback.percentage.toString() }
      );
    },
    addBlank() {
      const editor = window.CKEDITOR.instances['ckeditorElement'];

      const selectedText = editor.getSelection().getSelectedText();

      const blank = selectedText.replace(
        selectedText.trim(),
        '*' + selectedText.trim() + '*'
      );

      editor.insertText(blank);

      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: editor.getData(),
        },
        undoBatch: { type: 'editFillInTheBlanksTemplate' },
      });
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
});
</script>

<style scoped>
.h5pEditorTopPanel {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}

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
