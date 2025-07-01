<template>
    <div>
            <slot></slot>
    </div>
</template>
<script>
import Sortable from 'sortablejs';
import Collection from './Collection.vue'
import Vue from 'vue'
export default {
    props: ['dataPrototype', 'sortable', 'allowAdd', 'allowDelete'],
    data() {
       
        return {
            counter: 0,
            sort : null
        }
    },
    mounted() {
        this.counter = this.$el.querySelectorAll('.collection-item').length
        this.bind(this.$el)
        if (this.$el.querySelector('.add-button')) {
            this.$el.querySelector('.add-button').addEventListener('click', this.onAdd)
        }
        if (this.sortable) {
            this.sort = Sortable.create(this.$el.querySelector('.collection-list'),{
                onEnd : this.onSort
            })
        }
    },
    beforeDestroy() {
        this.$el.querySelector('.add-button').removeEventListener('click', this.onAdd)
        this.$el.querySelectorAll('.remove-button').forEach(c => c.removeEventListener('click', this.onRemove))
    },
    methods: {
        onSort() {
            this.$el.querySelectorAll('.collection-item').forEach((item, index) => {
                item.querySelector('input[name*=' + this.sortable + ']').value = index + 1
            })
        },
        bind(parent) {
            parent.querySelectorAll('.remove-button').forEach(c => c.addEventListener('click', this.onRemove))
        },
        onAdd(ev) {
            var template = document.createElement('template');
            template.innerHTML = '<div class="collection-item">' + this.dataPrototype.replaceAll(/__name__/g, this.counter++).trim() + (this.allowDelete ? ' <button type="button" class="button button-red button-small remove-button"><i class="icon-bin"></i></button>' : '')  + '</div>'
            this.bind(template.content.firstChild)
            
            this.$el.querySelector('.collection-list').append(template.content.firstChild)
            let list = this.$el.querySelectorAll('.collection-item');
            list[list.length-1].querySelector('input, textarea').focus()
            if (this.sortable) {
                list[list.length-1].querySelector('input[name*=' + this.sortable + ']').value = list.length
            }
            list[list.length-1].querySelectorAll('collection').forEach(coll => {
                let cmp = Vue.extend({
                    template:  coll.outerHTML
                })
                let tmp = new cmp()
                tmp.$mount(coll)
                /*let tmp = new cmp({
                    propsData: {
                        sortable : coll[':sortable'],
                        dataPrototype : coll['data-prototype']
                    },
                    el: coll
                })*/
            })
        },
        onRemove(ev) {
            let target = ev.target
            while(target && !target.classList.contains('remove-button')) {
                target = target.parentElement
            }
            
            target.parentElement.remove()
        }
    }
}
</script>