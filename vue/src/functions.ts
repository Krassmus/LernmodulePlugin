import { $gettext } from '@/language/gettext';

export function formatInvalidTaskDefinitionErrorMessage(
  e: unknown,
  task_json: unknown
): string {
  return (
    $gettext(
      'Diese Aufgabe konnte nicht geladen werden. ' +
        'Beim Einlesen von task_json ist ein Fehler vorgekommen. Fehler: '
    ) +
    '\n' +
    e +
    '\n' +
    'task_json: ' +
    JSON.stringify(task_json, null, 2)
  );
}
