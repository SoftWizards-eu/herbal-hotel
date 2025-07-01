<template>
    <span>
        <span  @click.prevent.stop="onClick"><slot></slot></span>
        <confirm v-if="showModal" @close="onClose" @confirm="onConfirm" :title="title"></confirm>
    </span>
</template>
<script> 
    export default {
        name: 'confirm-link',
        props: {
            title: {
                default: null
            },
            href: {
                default: null
            },
            callback: {
                default: null
            }
        },
        data() {
            return {
                showModal : false
            }
        },
        methods: {
            onConfirm() {
                if (this.href) {
                    window.location = this.href
                }
                this.showModal = false
                this.$emit('confirm')
            },
            onClose() {
                this.$emit('close')
                this.showModal = false
            },
            onClick() {
                this.showModal = true
            }
        }
    }
</script>
