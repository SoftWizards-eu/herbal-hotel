import Vue from 'vue'
import VueI18n from 'vue-i18n'
import axios from 'axios'
const locale = document.documentElement.lang || window.i18n.locale
let messages = {}
messages[locale] = window.i18n.domain['admin']

let numberFormats = {}
numberFormats[locale] = {
    currency: {
      style: 'currency', currency: 'PLN', currencyDisplay :'symbol'
    },
    stat : {
        style: 'decimal',
        minimumFractionDigits : 2,
        maximumFractionDigits : 2
        
    }
}
Vue.use(VueI18n)
export default new VueI18n({
    locale: locale,
    messages: messages,
    numberFormats: numberFormats
})