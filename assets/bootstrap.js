import { startStimulusApp } from '@symfony/stimulus-bundle';
import Timeago from 'stimulus-timeago'

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.debug = false;
app.register('timeago', Timeago)
