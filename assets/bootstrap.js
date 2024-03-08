import { startStimulusApp } from '@symfony/stimulus-bundle';
import Timeago from 'stimulus-timeago';

console.log('starting stimulus...');

const app = startStimulusApp();
app.debug = true;
app.register('timeago', Timeago)


