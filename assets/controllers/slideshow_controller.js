import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['slideshow']
    // ...

    connect() {
        super.connect();
        console.log('hello from ' + this.identifier);
        var splide = new Splide( this.slideshowTarget).mount();
    }
}
