import { Module, VuexModule, Mutation, Action } from 'vuex-module-decorators';

@Module({ name: 'requestQueue' })
export class RequestQueueModule extends VuexModule {
  queue: Array<() => Promise<void>> = [];
  active = 0;
  maxConcurrent = 12;
  delay = 0;

  // Mutation to add a request to the queue
  @Mutation
  addToQueue(request: () => Promise<void>) {
    this.queue.push(request);
  }

  // Mutation to update active request count
  @Mutation
  incrementActive() {
    this.active++;
  }

  @Mutation
  decrementActive() {
    this.active--;
  }

  // Action to handle queue processing
  @Action
  async processQueue() {
    if (this.active >= this.maxConcurrent || this.queue.length === 0) {
      return;
    }

    const request = this.queue.shift();
    if (request) {
      this.context.commit('incrementActive');

      try {
        await request();
      } finally {
        this.context.commit('decrementActive');

        if (this.delay) {
          await new Promise((resolve) => setTimeout(resolve, this.delay));
        }

        await this.processQueue(); // Recursively process the next request
      }
    }
  }

  // Action to add a request and trigger queue processing
  @Action
  async enqueue(request: () => Promise<void>) {
    this.context.commit('addToQueue', request);
    await this.processQueue();
  }
}
