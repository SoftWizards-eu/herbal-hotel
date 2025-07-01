<template>
    <div class="block kr">
        <slot></slot>
        <a href="#" class="more" v-if="expanded" @click.prevent="collapse">schowaj ({{hidden}})</a>
        <a href="#" class="more" v-else-if="hidden > 0" @click.prevent="expand"> więcej ({{hidden}}) </a>
    </div>
</template>
<script>
export default {
    data() {
        return {
            expanded: false,
            hasMany : false,
            hidden : 0
        }
    },
    mounted() {
        if(this.$slots.default[2].children.length < 6) {
            return
        }
        this.$nextTick(() => {
            this.collapse()
        })
    },
    methods: {
        collapse() {
            this.hasMany = this.$slots.default[2].children.length
            let num = 0
            this.$slots.default[2].children.forEach(c => {
                if (c.tag != 'li') {
                    return;
                }
                
                let isChecked = c.elm.querySelector('.checked') != null
                if (num < 5 || isChecked) {
                    num++
                    return;
                }
                this.hidden ++;
                c.elm.classList.add('hidden')
                num++
            })
        },
        expand() {
            this.expanded = true
            this.$slots.default[2].children.forEach(c => {
                if (c.tag != 'li') {
                    return;
                }
                
                
                c.elm.classList.remove('hidden')
            })
        }
    }
}
</script>