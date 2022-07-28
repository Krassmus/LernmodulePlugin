import { TaskDefinition } from '@/models/TaskDefinition';

/**
 * @param taskDefinition The definition of the task
 * @param moduleName The name of the Lernmodul
 * @param infoText The HTML description of the Lernmodul
 * @param module_id The id from lernmodule_module
 * @param block_id The id from lernmodule_blocks where the module is to be
 *                 inserted.  (This only applies for newly created modules.)
 */
export async function saveTask(
  taskDefinition: TaskDefinition,
  moduleName: string,
  infoText: string,
  module_id: string,
  block_id?: string
): Promise<SaveTaskResponse> {
  const url = window.STUDIP.LernmoduleVueJS.saveRoute;
  const token = window.STUDIP.CSRF_TOKEN;
  const formData = new FormData();
  // TODO Maybe we should parse taskDefinition here to ensure it has no extra
  //   fields we didn't intend to save.
  formData.append('task_definition', JSON.stringify(taskDefinition));
  formData.append('module_id', module_id);
  formData.append('name', moduleName);
  formData.append('infotext', infoText);
  if (block_id) {
    formData.append('block_id', block_id);
  }
  formData.append(token.name, token.value);
  return fetch(url, {
    method: 'POST',
    body: formData,
  }).then((response) => {
    if (!response.ok) {
      throw new Error(response.statusText);
    }
    // TODO Maybe we should parse the server's response to ensure it really is
    //  an instance of SaveTaskResponse.
    return response.json() as Promise<SaveTaskResponse>;
  });
}

export interface SaveTaskResponse {
  status: 'success';
  taskDefinition: TaskDefinition;
  moduleName: string;
  infotext: string;
}
