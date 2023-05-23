import Vue from 'vue';
import App from './App.vue';

if (window.STUDIP) {
  console.info('doing nothing because we are in studip');
} else {
  console.info('mounting component for testing');
  // Only run if not inside of Stud.IP
  new Vue({
    render: (h) => h(App),
  }).$mount('#app');
}
