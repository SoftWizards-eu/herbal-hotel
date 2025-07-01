<template>
    <form @submit="onSubmit" :method="method" :action="action" @click="ignore = true">
        <div class="search">
            <div class="bg">
                <input type="text" class="txt" @click.stop=""  @focus="focus=true" v-model="query" name="query" placeholder="Wpisz numer, nazwę, VIN..." autocomplete="off" />
                <div class="cat" @click.stop="selectContext = true">
                    <span class="info">{{ contexts[selectedContext] }}</span>
                    <div class="list-context" v-if="selectContext">
                        <span v-for="(lvl, id) in contexts" @click.stop="selectedContext = id, selectContext = false" :key="id">{{lvl}}</span>
                    </div>
                </div>
                <button type="submit" class="btn" value=""></button>
            </div>
            <div class="list" v-if="list.length > 0">
                <a :href="product.url" v-for="product in list" :key="product.id">
                    <text-highlight :queries="query">{{ product.title }}</text-highlight> <small><text-highlight :queries="query">{{ product.index }} </text-highlight></small>
                </a>
            </div>
            <div class="list other" v-show="focus && list.length == 0 && query.length == 0 && history.length">
                <strong>Ostatnio szukane</strong>
                <a :href="hist.href" v-for="hist in history" :key="hist.href">{{hist.name}}</a>
            </div>
            
        </div>
        <alert v-if="alert" @close="alert=null" :text="alert">
        </alert>
        <input type="hidden" name="mode" value="vin" v-if="selectedContext == 'vin'" />
        <input type="hidden" name="mode" value="products" v-if="selectedContext == 'products'" />
        <template v-if="selectedContext=='filtered'">
            <input type="hidden" v-for="(v, k) in filter.query" :key="k" :name="k" :value="v" />
        </template>
    </form>
</template>
<script>
    import Modal from '../util/Modal'
    import Alert from '../util/Alert'
    import TextHighlight from 'vue-text-highlight';
    import vin from '../service/vin'
    export default {
        props: ['method', 'action', 'search', 'last', 'main'],
        components: {Modal, Alert, TextHighlight},
        mounted() {
            this.$bus.$on('bodyclick', this.onBody);
            this.$slots.default[0].children[2].children.forEach(x => {
                if (x.tag == 'a') {
                    this.history.push({
                        href : x.data.attrs.href,
                        name : x.children[0].text
                    })
                }
            })
        },
        beforeDestroy() {
            this.$bus.$off('bodyclick', this.onBody);
        },
        data() {
            return {
                query : this.search,
                alert : false,
                list : [],
                history: [],
                focus : false,
                ignore: false,
                selectContext: false,
                selectedContext: (this.main && this.main.levels.length) ? 'filtered' : 'all'
            }
        },
        methods: {
            onSubmit(e) {
                if (this.query.length < 3) {
                    e.preventDefault()
                    this.alert= 'Wpisz min. 3 znaki'
                    return;
                }
                if (this.selectedContext == 'vin' && !vin.validate(this.query)) {
                    this.alert = 'Nieprawidłowy VIN'
                    e.preventDefault()
                    return;
                }
                let goVin = this.selectedContext == 'vin'
                if (vin.validate(this.query)) {
                    goVin = true
                }
                
                if (goVin) {
                    this.$bus.$emit('vin', this.query);
                    e.preventDefault()
                    return;
                }
                
            },
           
            onBody(e) {
                this.selectContext= false
                if (this.ignore || this.focus) {
                    this.ignore = false;
                    this.focus = false
                    return;
                }
                this.list = [];
                this.focus = false
            },
            serialize(obj) {
              var str = [];
              for (var p in obj)
                if (obj.hasOwnProperty(p)) {
                  str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
              return str.join("&");
            }
            
        },
        computed: {
             filter() {
                if (this.main && this.main.levels.length) {
                    return this.main.levels[this.main.levels.length-1];
                }
                
                return null;
            },
            contexts() {
                let contexts = {
                    all : 'wszędzie',
                    products: 'numery porównawcze',
                    vin : 'VIN'
                };
                if (this.main && this.main.levels.length) {
                    contexts['filtered'] = {vehicle: 'wybrany pojazd', axle: 'wybrana oś', transmission : 'wybrana skrzynia', engine : 'wybrany silnik'}[this.main.type]
                    
                }
                
                return contexts
            }
        },
        watch: {
            query() {
                if (this.query == '') {
                    this.list = [];
                } else {
                    this.$http.get(document.querySelector('#top form').action, {params: {query : this.query, quick : 1}}).then(resp => {
                        this.list = resp.data.list || []
                    })
                }
            }
        }
    }; 
</script>
<style lang="scss" scoped>
.search {
    position: relative;
    z-index: 500;
    .list {
        position: absolute;
        padding: 5px 15px;
        top: 100%;
        width: 100%;
        left: 0px;
        box-sizing: border-box;
        background: white;
        border-radius: 3px;
        font-size: 14px;
        small {
            font-size: 12px;
        }
        strong {
            margin-bottom: 10px;
            display: block;
        } 
        border: 1px solid #cecece;
        a {
            display: block;
            padding: 5px 0px;
            text-decoration :none;
            color: #5d5d5d;
            &:hover {
                text-decoration: underline;
            }
        }
    }
    
}
</style>