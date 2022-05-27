/**
 * @param id The ID of the task being saved
 * @param taskDefinition The definition of the task
 */
export async function saveTask(
  id: string | null,
  taskDefinition: TaskDefinition
): Promise<SaveTaskResponse> {
  const url = window.STUDIP.LernmoduleVueJS.saveRoute;
  const token = window.STUDIP.CSRF_TOKEN;
  const formData = new FormData();
  // TODO Maybe we should parse taskDefinition here to ensure it has no extra
  //   fields we didn't intend to save.
  formData.append('task_definition', JSON.stringify(taskDefinition));
  if (id) {
    formData.append('id', id);
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
}

export type TaskDefinition = FillInTheBlanks;
export type FillInTheBlanks = {
  type: 'FILL_IN_THE_BLANKS';
  template: string;
};
