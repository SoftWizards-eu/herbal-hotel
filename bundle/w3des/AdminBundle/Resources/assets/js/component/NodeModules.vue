<template>
    <div>
        <div class="content-box">
            <slot></slot>
            <span v-for="m in modules" :key="m.name">
                <span class="button" @click="onCreate(m.name)" > {{ m.label }}</span>&nbsp;
            </span>
        </div>
        <keep-alive>
            <draggable v-model="additions" handle=".module-title">
                <div v-for="m in additions" :key="m.id" class="content-box">
                    <component :is="m.component" @remove="onRemove(m)">
                        
                    </component>
                </div> 
            </draggable>
        </keep-alive>
    </div>
</template>
<script>
import draggable from 'vuedraggable'
import Vue from 'vue'
export default {
    props: {
        count: {
            type: Number,
            default: 0
        },
        modules: {
            type: Object,
            default: null
        },
        prototypes: {
            
        },
        initial: {
            type: Array,
            default() {
                return []
            }
        }
    },
    components: {draggable},
    data() {
        let list = []
        this.initial.forEach(x => {
            let c = Vue.extend({
                template : x.template.trim()
            })
            x.component = c
            list.push(x)
        })
        return {
            counter : this.count,
            additions : list
        }
    },
    methods: {
        onCreate(name) {
            let c = this.counter++
            this.additions.push({
                id  : c,
                name: name,
                component : Vue.extend({
                    template : this.prototypes[name].replaceAll(/_prototype_/g, c)
                })
            })
        },
        onRemove(row) {
            this.additions = this.additions.filter(a => a != row)
        }
    }
}
</script>
<style type="scss">

</style>
