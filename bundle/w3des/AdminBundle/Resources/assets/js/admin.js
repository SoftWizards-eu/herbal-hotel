import '@openfonts/open-sans_all'
import '@openfonts/roboto_all'
import '@openfonts/roboto-slab_all'
import 'modern-css-reset'

import '~/style/admin.scss'

import i18n from '~/js/service/i18n'


import ckeditor from '~/js/widget/CKEditor'
import axios from 'axios';
import Vue from 'vue'; 
import VueAxios from 'vue-axios'

import { EventBus } from '~/js/service/bus.js';
Vue.prototype.$bus = EventBus;

Vue.use(VueAxios, axios)

import VTooltip from 'v-tooltip'

Vue.use(VTooltip)

import DynamicTable from '~/js/widget/Table'

import NodeTree from '~/js/component/NodeTree'



import Confirm from '~/js/widget/Confirm'
import ConfirmLink from '~/js/widget/ConfirmLink'
import Modal from '~/js/widget/Modal'
import NodeModule from '~/js/component/NodeModule'
import NodeModules from '~/js/component/NodeModules'
import NodeSections from '~/js/component/NodeSections'

import EmbedNodeButton from '~/js/component/EmbedNodeButton'

import Gallery from '~/js/component/Gallery'
import GalleryItem from '~/js/component/GalleryItem'

import Collection from '~/js/component/Collection'
import DynamicClass from '~/js/widget/DynamicClass'

Vue.component('modal', Modal)
Vue.component('confirm', Confirm)
Vue.component('confirm-link', ConfirmLink)

Vue.component('node-modules', NodeModules)
Vue.component('node-module', NodeModule)
Vue.component('node-sections', NodeSections)
Vue.component('ckeditor', ckeditor)
Vue.component('gallery', Gallery)
Vue.component('dynamic-class', DynamicClass)
Vue.component('gallery-item', GalleryItem)

Vue.component('embed-node-button', EmbedNodeButton)

Vue.component('collection', Collection)

var APP = new Vue({
    el : '#outer',
    i18n,
    components: {DynamicTable, 'node-tree' : NodeTree},
    delimiters: ['${', '}'],
    computed: {
        console: () => console,
        window: () => window,
    },
    data() {
        return {
            
        }
    },
    mounted() {
        document.body.addEventListener('click', this.onBodyClick)
        if (window.afterMount) {
            window.afterMount.call(this)
        }
    },
    beforeDestroy() {
        document.body.removeEventListener('click', this.onBodyClick)
    },
    methods: {
        onBodyClick(ev) {
            this.$bus.$emit('body', ev)
        },
        confirm(text, url) 
        {
            if (confirm(text)) {
                window.location = url
            }
        }
    }
})