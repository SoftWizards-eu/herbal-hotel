<template>
    <div :class="'button-dropdown'" @click.stop="onClick">
        <slot></slot>
        <ul class="dropdown" ref="dropdown" v-if="dropdown" @click.stop="">
            <li v-for="t in embed" :key="t" @click="onSelect(t)">
                {{ $t('node.' + t) }}
            </li>
        </ul>
    </div>
</template>
<script>
import Vue from 'vue'
export default {
    props: ['embed'],
    data() {
        return {
            dropdown: false
        }
    },
    mounted() {
        this.$bus.$on('body', this.stop)
    },
    beforeDestroy() {
        this.$bus.$off('body', this.stop)
    },
    methods: {
        onClick() {
            if (!this.dropdown) {
                this.start()
            } else {
                this.stop()
            }
        },
        onSelect(type) {
            this.$emit('selected', type)
            this.stop()
        },
        start() {
            this.$bus.$emit('body')
            if (this.embed.length == 1) {
                return this.onSelect(this.embed[0])
            }
            this.$el.classList.add('button-dropdown-show')
            this.dropdown = true
        },
        stop() {
            this.$el.classList.remove('button-dropdown-show')
            this.dropdown = false
        }
    }
}
</script>