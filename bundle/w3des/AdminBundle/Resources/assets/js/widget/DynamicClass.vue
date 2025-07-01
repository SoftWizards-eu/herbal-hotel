<template>
    <div :class="selected">
        <slot></slot>
    </div>
</template>
<script> 
    export default {
        props: {
            select: {
                default: null
            }
        },
        data() {
            return {
                selected : 'sel-'  + this.value()
            }
        },
        mounted() {
            this.$nextTick(() => {
                if (document.getElementById(this.select)) {
                    document.getElementById(this.select).addEventListener('change', this.onChange)
                    this.onChange()
                }
            })
        },
        methods: {
            value() {
                let sel = document.getElementById(this.select)
                if (sel && sel.selectedIndex && sel.options[sel.selectedIndex]) {
                    return sel.options[sel.selectedIndex].value
                }
                
                return null
            },
            onChange() {
                this.selected = 'sel-' + this.value()
            }
        }
    }
</script>
<style lang="scss">
</style>