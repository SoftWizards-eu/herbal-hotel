<template>
    <div>
        <div class="img">
        <transition name="fade" v-for="(sl, i) in filtered" :key="'trans_' + i">
                <video class="nocontrols" width="100%" height="100%" playsinline autoplay  loop v-if="sl.video && sl.active" :key="'video_' + i">
                  <source :src="sl.photo.path" :type="sl.photo.mime" />
                  <source v-if="sl.second" :src="sl.second.path" :type="sl.second.mime" />
                </video>
                <div v-if="!sl.video" v-show="sl.active" class="bg" :style="'background-image: url(\'' + sl.photo.path + '\')'" :key="'img_' + i" ></div>
            </transition>
            
            <span></span>
        </div>
        <div class="wrap" v-if="slide.content">
            <div class="content">
                <div class="text-content slider-content" v-html="slide.content"></div>
            </div>
        </div>
        <a v-else :href="slide.url"><span class="wrap"></span></a>
        <span style="display:none" class="nav prev" @click="goPrev()"><i class="icon-arrow-left4"></i></span>
        <span style="display:none" class="nav next" @click="goNext()"><i class="icon-arrow-right4"></i></span>
        <div  style="display:none" class="btn-wrap">
            <a style="display:none" href="#content-start" @click.stop.prevent="scroll"><i class="icon-arrow-down4"></i></a>
        </div>
    </div>
</template>
<script> 
    export default {
        name :'slider',
        props : {
            cfg : {type: Array},
            mobile : {default: 800}
        },
        mounted() {
            this.startLoop()
            window.addEventListener('resize', this.onResize)
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.onResize)
        },
        data : function() {
            return {
                index : 0,
                loop : null,
                mobileBrowser : this.isMobile()
            }
        },
        computed: {
            slide() {
                return this.cfg[this.index]
            },
            filtered() {
                let result = []
                this.cfg.forEach((item, index) => {
                    let tmp = {...item, active : index == this.index }
                    if (this.mobileBrowser && tmp.mobile_photo) {
                        tmp.photo = tmp.mobile_photo
                        tmp.second = tmp.mobile_second
                    }
                    tmp.video = tmp.photo.mime.indexOf('video') !== -1
                    result.push(tmp)
                })
                return result
            }
        },
        methods: {
            scroll() {
                window.scrollTo({
                    top: 1040 - 182,
                    behavior: 'smooth'
                })
            },
            onResize() {
                this.mobileBrowser = this.isMobile()
            },
            isMobile() {
                return document.body.offsetWidth < this.mobile
            },
            startLoop() {
                if (this.loop) {
                    clearTimeout(this.loop)
                    this.loop = null
                }
                if (this.cfg.length > 0) {
                    this.loop = setTimeout(() => {
                        this.goNext()
                    }, 10000)
                }
            },
            goNext() {
                this.startLoop()
                if (this.index < this.cfg.length - 1) {
                    this.index++
                } else {
                    this.index = 0
                }
            },
            goPrev() {
                this.startLoop()
                if (this.index > 0 ) {
                    this.index--
                } else {
                    this.index = this.cfg.length - 1
                }
            }
        }
    }
</script>
<style>
.nocontrols::-webkit-media-controls {
  display: none;
}
</style>