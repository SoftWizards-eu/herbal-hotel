<template>
    <div class="gallery">
         <div class="list">
            <div v-for="(img, i) in images" :class="i == active ? 'active' : ''" :key="i"><img :src="img.thumb"  @click="active = i" /></div>
         </div>
         <div class="main">
            <div>
                <div v-for="(img, i) in images" :class="i == active ? 'active' : ''" :key="i"><img :src="img.big" @click="show=true" v-if="img.type == 'image'" /><iframe :src="img.big" v-else /></div>
            </div>
         </div>
         <LightBox v-if="show" :startAt="images[active].real" :media="media" @onClosed="show = false"></LightBox>
    </div>
</template>
<script>
    import LightBox from 'vue-image-lightbox'
    export default {
        props: {
            showtec: null
        },
        components :{ LightBox },
        mounted() {
            var collect = [];
            var num = 0;
            if (!this.$slots.default[0].children) {
                return;
            }
            let real = -1
            this.$slots.default[0].children.forEach((ch) => {
                let big = null
                if (this.$slots.default[1].children[num].children) {
                    big = this.$slots.default[1].children[num].children[0].data.attrs.src
                    real++
                } else {
                    big = this.$slots.default[1].children[num].data.attrs['data-path'].replace('external/', 'https://')
                }
                var data = {
                    thumb : ch.children[0].data.attrs.src,
                    src : this.$slots.default[1].children[num].data.attrs['data-zoom'],
                    type : this.$slots.default[1].children[num].data.attrs['data-type'],
                    big : big,
                    real : real
                };
                num++
                collect.push(data)
            })
            this.images = collect;
        },
        computed: {
            media() {
                let ph = []
                this.images.forEach(m => {
                    if (m.type == 'image') {
                        ph.push(m)
                    }
                })
                return ph
            }
        },
        data() {
            return {
                active : 0,
                images : [],
                show : false
                
            }
        },
        methods: {
        },
        watch: {
        }
    }
</script>
<style lang="scss">
     
     .gallery {
        display: flex;
        height: 460px;
        &.one-item {
            height: auto;
        }
        .list {
            flex: 0 0 130px; 
            margin-right: 10px;
            flex-basis: 130px;
            overflow: auto;
        }
        .list div {
            cursor: pointer;
            border: 1px solid #d6d6d6;
            border-radius: 3px;
            margin-bottom: 12px;
            transition: border-color .4s;
            img {
                width: 100%;
                display: block;
            }
            &:hover, &.active {
                border-color: #164369
            }
        }
        .main {
            flex: 1;
            position: relative;
            > div {
                div {
                    display: none;
                    border-radius: 3px;
                    border: 1px solid #d6d6d6;
                    img, iframe {
                        display: block;
                        width: 100%;
                    }
                    iframe {
                        height: 460px;
                        border: 0px solid;
                    }
                    &.active {
                        display: block;
                    }
                }
            }
        }
    }
</style>
