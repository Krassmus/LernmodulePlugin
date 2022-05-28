/**
 * @param id The ID of the task being saved
 * @param taskDefinition The definition of the task
 */
import { TaskDefinition } from '@/models/TaskDefinition';

export async function saveTask(
  taskDefinition: TaskDefinition,
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
    //  In this case, it might be overkill -- the server either returns an HTTP
    //  error code or { status: 'success' } -- but in a more complex API, it
    //  would be desirable to validate the data we receive from the server.
    return response.json() as Promise<SaveTaskResponse>;
  });
}

export interface SaveTaskResponse {
  status: 'success';
  taskDefinition: TaskDefinition;
}
