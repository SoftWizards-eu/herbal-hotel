<template>
    <div>
        <slot></slot>
        <input type="hidden" name="tab_position" ref="position" />
    </div>
</template>
<script>

export default {
    props: {
        active: {
            type: String
        }
    },
    data() {
        return {
            tabPosition : null
        }
    },
    methods: {
        onHash(hash) {
            if (hash == '' || hash == '#' ) {
                return
            }
            if (!this.navLinks[hash]) {
                return
            }
            this.contents.forEach(cn => {
                if (cn.id && cn.id == hash.substring(1)) {
                     cn.style.display = 'block'
                     this.navLinks['#' + cn.id].classList.add('active')
                     this.$refs.position.value = hash
                    
                } else {
                     cn.style.display = 'none'
                     this.navLinks['#' + cn.id].classList.remove('active')
                }
            })
        },
        onHashChange() {
            this.onHash(window.location.hash)
        }
    },
    computed: {
        navLinks() {
            let links = {}
            this.$el.querySelector('nav').querySelectorAll('.tab-item').forEach(a => {
                links[a.getAttribute('href')] = a
            })
            return links;
        },
        contents() {
            return this.$el.querySelectorAll('.tab-content');
        }
    },
    mounted() {
        window.addEventListener('hashchange', this.onHashChange)
        if (this.active && this.active != '') {
            window.location.hash = this.active
        } else {
            this.onHash(window.location.hash)
        }
    },
    beforeDestroy() {
        window.removeEventListener('hashchange', this.onHashChange)
    }
}
</script>
<style type="scss">

</style>
