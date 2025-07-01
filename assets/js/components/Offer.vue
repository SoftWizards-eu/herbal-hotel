<template>
    <modal @close="$emit('close')" container-class="offer-form-container">
        <template v-slot:header>
            <nav>
                <a href="#" @click.stop="form = 'offer'" :class="form == 'offer' ? 'active' : ''">
                    Zapytanie ofertowe
                </a>
                <a href="#" @click.stop="form = 'service'" :class="form == 'service' ? 'active' : ''">
                    Zapytanie o serwis
                </a>
            </nav>
            <a href="#" @click.prevent="$emit('close')" class="close"><i class="icon-cancel-circle"></i></a>
        </template>
        <template v-slot:body>
            <div class="" v-show="form == 'offer'">
                <slot name="offer"></slot>
            </div>
            <div class="" v-show="form == 'service'">
                <slot name="service"></slot>
            </div>
        </template>
    </modal>
</template>
<script>
    import Modal from '../util/Modal'
    export default {
        components: {Modal},
        data() {
            return {
                open : false,
                form : 'offer'
            }
        },
        created() {
        },
        beforeDestroy() {
        },
        watch: {
        },
        computed: {
        }
    }; 
</script>
<style lang="scss">
.offer-form-container {
    .modal-container {
        width: 1080px;
        padding: 0px;
        background: none;
        .modal-body {
            background: white;
            padding: 40px;
            margin: 0px;
        }
        .modal-header {
            position: relative;
            .close {
                position: absolute;
                top: 30px;
                right: 30px;
                color: white;
                font-size: 30px;
            }
            nav {
                display: flex;
            }
            nav a {
                display: inline-block;
                height: 80px;
                line-height: 80px;
                padding: 0px 40px;
                font-family: Merriweather;
                color: white;
                background: #009fe3;
                &.active {
                    background: white;
                    color: #252525;
                }
            }
        }
    }
}
</style>