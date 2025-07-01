/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import AOS from 'aos';
import 'aos/dist/aos.css'; // You can also use <link> for styles
// ..
AOS.init();
import './content.js'
import './styles/app.scss';
import CookieLaw from 'vue-cookie-law'

import axios from 'axios';
import Vue from 'vue'; 
import VueAxios from 'vue-axios'

import { EventBus } from './js/service/bus.js';
import Gallery from './js/components/Gallery.vue';
import MapModule from './js/components/Map.vue';
import OfferModule from './js/components/Offer.vue';
import Alert from './js/util/Alert.vue'

import Recaptcha from './js/components/Recaptcha.vue';
Vue.component('recaptcha', Recaptcha)
Vue.component('recaptcha-token', Recaptcha)
import VueLazyLoad from 'vue-lazyload'
Vue.use(VueLazyLoad)



Vue.prototype.$bus = EventBus;

Vue.use(VueAxios, axios)

import VueAgile from 'vue-agile'
Vue.use(VueAgile)

Vue.axios.defaults.baseURL = window.base_url + '/'
Vue.axios.defaults.headers.common['Content-Type'] = 'application/json';
Vue.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.component('alert', Alert)
import VEmbed from 'v-video-embed'
Vue.use(VEmbed);
import Embed from './js/util/Embed.vue'
Vue.component('oembed', Embed)

import VueSlickCarousel from 'vue-slick-carousel'


import VueSilentbox from 'vue-silentbox'

Vue.use(VueSilentbox)


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';


import Slider from './js/components/Slider.vue';
new Vue({
    el : '#outer',
    delimiters: ["${", "}"],
    components: { Gallery, MapModule, Slider, 'carousel' : VueSlickCarousel, OfferModule, CookieLaw},
    created() {
        if (document.body.classList.contains('home')) {
            document.body.classList.add('before-animation')
            this.animateBlocks = true
        }
    },
    mounted() {
        this.$el.addEventListener('click', this.onClick);
        var openTab = function(id) {
            document.querySelectorAll('.tabs nav a').forEach(x => {x.classList.remove('active'); x.classList.add('disabled')})
            document.querySelectorAll('.tabs .contents .tab').forEach(x => {x.classList.remove('active'); x.classList.add('disabled')})
            
            let element = document.getElementById(id)
            element.classList.remove('disabled');
            element.classList.add('active');
            
            element = document.querySelector('.tabs nav a[href="#' + id + '"]')
            element.classList.remove('disabled');
            element.classList.add('active');
        }
        document.querySelectorAll('.tabs nav a').forEach((x) => {
            x.addEventListener('click' , function(e) {
                e.stopPropagation();
                e.preventDefault();
                openTab(this.href.split('#')[1])
                return false;
            })
        })
        document.addEventListener('scroll', this.onScroll, {
            passive: true
        })
        this.onScroll()
        document.querySelectorAll('header menu .has-child > a').forEach(x => {
            x.addEventListener('click', function(e) {
                if (window.innerWidth <= 1260) {
                    if (!e.target.parentNode.classList.contains('opened')) {
                        e.stopPropagation()
                        e.preventDefault();
                        
                        document.querySelectorAll('header menu .opened').forEach(x => {
                            x.classList.remove('opened')
                        })
                        e.target.parentNode.classList.add('opened')
                        
                        return false
                    }
                    
                    
                    
                    
                    
                }
                
            } )
        })
        
    },
    beforeDestroy() {
        document.removeEventListener('scroll', this.onScroll)
    },
    data() {
        return {animateBlocks : false, toAnimate : [], marker : -1, jobVisible: null, gal: false, galStart : 0, showOffer : false, covers: {}, modal : null, search: false, realization : 0}
    },
    beforeDestroy() {
        this.$el.removeEventListener('click', this.onClick);
    },
    methods : {
        goRealization(direction) {
            let items = this.$refs.realizations.querySelectorAll('.home-realization-item')
            items[this.realization].classList.remove('active')
            this.realization += direction
            if (this.realization < 0) {
                this.realization = items.length - 1
            } else if (this.realization >= items.length) {
                this.realization = 0
            }
            items[this.realization].classList.add('active')
        },
        onClick() {
            this.$bus.$emit('bodyclick');
        },
        onScroll(e) {
           
            let threshold = 50; 
            
            let contentStart = 110;
            let elem = document.getElementById('content');
        
            if (elem) {
                contentStart = elem.getBoundingClientRect().top;
            }
            
            if (contentStart < threshold || window.pageYOffset > 50) {
                document.body.classList.add('scrolled');
            } else {
                document.body.classList.remove('scrolled');
            }
        },
        menuToggle() {
            if (document.body.classList.contains('menu-open')) {
                document.body.classList.remove('menu-open')
            } else {
                document.body.classList.add('menu-open')
            }
        },
        reservation() {
            //Booking.Open()
        },
        alert(e) {
            alert(e)
        },
        console(e) {
            console.log(e)
        },
        goSearch() {
            
            if (this.search) {
                this.$refs.searchForm.submit()
            } else {
                this.search = !this.search
                this.$nextTick(() => {
                    this.$refs.search.focus()
                })
            }
        },
        stopSearch() {
            this.search = false
        },
        showGal(id, index) {
            this.galStart = index
            this.gal = false
            this.$nextTick(() => {
                this.gal = id
                this.$nextTick(() => {
                    let c = document.querySelector('#silentbox-gallery').__vue__
                    c.openOverlay(c.gallery[index], index)
                    this.$nextTick(() => {
                        this.$el.querySelectorAll('#silentbox-overlay__embed img, #silentbox-overlay__embed video, #silentbox-overlay__description').forEach(x => {
                            x.addEventListener('click', this.stopEvent)
                        })
                    })
                })
            })
        },
        stopEvent(e) {
            e.preventDefault()
            e.stopPropagation()
        },
        galEnabled(id) {
            return this.gal == id
        },
        hideGal(id) {
            this.gal = false
        }
    },
    computed: {
        console: () => console,
        window: () => window,
    }
})
