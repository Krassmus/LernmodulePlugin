import { $gettext } from '@/language/gettext';

export function formatInvalidTaskDefinitionErrorMessage(
  e: unknown,
  task_json: unknown
): string {
  return (
    $gettext(
      'Diese Aufgabe konnte nicht geladen werden. ' +
        'Beim Einlesen von task_json ist ein Fehler aufgetreten. Fehler: '
    ) +
    '\n' +
    e +
    '\n' +
    'task_json: ' +
    JSON.stringify(task_json, null, 2)
  );
}

export function formatResultMessage(
  resultMessage: string,
  score: number,
  maxScore: number
): string {
  // The H5P convention is to use :num as the placeholder for the user's score,
  // while the Stud5P convention is to use :correct. We support both in order
  // to be compatible with imported content from H5P.
  let result = resultMessage.replace(':correct', score.toString());
  result = result.replace(':num', score.toString());
  result = result.replace(':total', maxScore.toString());
  return result;
}
