import { createKitchenAlarm } from './dashboard-kitchen-alarm';
import { initEcho } from './echo';

window.createKitchenAlarm = createKitchenAlarm;

document.addEventListener('DOMContentLoaded', () => {
    initEcho();
});
