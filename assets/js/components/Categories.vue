<template>
    <nav id="categories">
        <div class="wrap">
            <template v-for="(cat, id ) in tree">
               <div class="spacer" v-if="id != 0" :key="'s' + id"></div>
                  <div :class="'item ' + (id > tree.length/2  ? 'right' : 'left')" :key="id" @mouseover="hover = []">
                     <a :href="cat.url">
                        <span class="border">
                            <i :class="'category-ico-' + cat.id"></i>
                            {{ cat.name }}
                        </span>
                     </a>
                     <div class="drop" @mouseover.stop="">
                        <div class="info">
                            <ul>
                                <li @mouseover.stop="hover =[]">
                                    <a :href="cat.url">
                                        <strong>{{cat.name}}</strong>
                                    </a>
                                </li>
                                <li v-for="sub in cat.children" :key="cat.id +'.' + sub.id" :class="'m' + (sub.children.length > 0 ? ' has-child' : '')" @mouseover.stop="onHover(sub, 0)">
                                    <a :href="sub.url">{{ sub.name }}<i></i></a>
                                </li>
                            </ul>
                            <div class="product" v-if="hover.length==0">
                                <strong>Polecany produkt</strong>
                            </div>
                            <ul class="sub-info" v-for="(cat, lvl) in hover" :key="'s' + cat.id">
                                <li>
                                    <a :href="cat.url">
                                        <strong>{{cat.name}}</strong>
                                    </a>
                                </li>
                                <li v-for="sub in cat.children" :key="cat.id +'.' + sub.id" :class="'m' + (sub.children.length > 0 ? ' has-child' : '')" @mouseover.stop="onHover(sub, lvl+1)">
                                    <a :href="sub.url">{{ sub.name }}<i></i></a>
                                </li>
                            </ul>
                        </div>
                     </div>
                </div>
            </template>
        </div>
    </nav>
</template>
<script>
export default {
    props: ['tree', 'products'],
    data() {
        return {
            hover: []
        }
    },
    mounted() {
        
    },
    methods: {
        product(id) {
            let cont = this.$slots.default[0].children[id * 2]
        },
        onHover(cat, lvl) {
            if (lvl == 0) {
                this.hover = []
            } else {
                this.hover = this.hover.slice(0, lvl)
            }
            if (cat.children.length >0) {
                this.hover[lvl] = cat
            }
        }
    }
}
</script>