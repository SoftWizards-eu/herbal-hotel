<template>
    <div :class="'combo' + (open ? ' combo-open' : '')" @click.prevent.stop="">
        <input type="text" v-model="txt" :placeholder="placeholderText" @focus="onFocus" ref="input"  class="input" :disabled="disabled" @keyup.down.prevent="goDown" @keyup.up.prevent="goUp" @keyup.enter.prevent="select" />
        <div class="list" v-if="open">
            <span :class="'item' + (pos == selected ? ' selected' : '')" v-for="(item, pos) in filteredList()" :key="pos" @click.prevent="click(item)">
                {{ item[displayField] }} 
            </span>
        </div>
    </div>
</template>
<script>
    export default {
        props: {placeholder : null, list : null, valueField : {default: 'id'}, displayField : {default: 'text'}, value : {default : ''}, disabled : {default: false}},
        data() {
            return {
                open : false,
                txt : '',
                selected: -1
            }
        },
        created() {
            
            this.$bus.$on('bodyclick', this.onBody);
        },
        beforeDestroy() {
            this.$bus.$off('bodyclick', this.onBody);
        },
        methods: {
            onFocus() {
                if (this.disabled) {
                    this.open = false
                } else {
                    this.open = true
                    this.selected = -1
                    
                    this.$refs.input.select()
                }
                
            },
            focus() {
                this.$refs.input.focus()
                this.onFocus()
            },
            onBody() {
                this.open = false
                this.txt = null
            },
            filteredList() {
                if (!this.txt || this.txt == '') {
                    return this.list
                }
                
                return this.list.filter(x => {
                    return x[this.displayField].toLowerCase().includes(this.txt.toLowerCase())
                })
            },
            goDown() {
                this.selected++
                if (this.selected >= this.filteredList().length) {
                    this.selected = 0;
                }
           },
           goUp() {
                this.selected--;
                if (this.selected <= 0) {
                    this.selected = 0;
                }
           },
           select() {
                if(this.selected == -1) {
                    return;
                }
                this.$emit('input', this.filteredList()[this.selected][this.valueField])
                this.onBody()
           },
           click(item) {
               this.$emit('input', item[this.valueField])
               this.onBody();
           }
        },
        watch: {
            txt() {
                this.selcted = -1;
            }
        },
        computed: {
            placeholderText() {
                let txt = this.placeholder;
                if (!this.open && this.value) {
                    this.list.forEach(x => {
                        if (x[this.valueField] == this.value) {
                            txt = x[this.displayField]
                        }
                    })
                }
                return txt
            }
        }
    }; 
</script>
<style lang="scss">
.combo {
    display: inline-block;
    position: relative;
    z-index: 200;
    .input {
        background: white url(../../images/select.png) no-repeat center right;
        color: #29497b;
        border: 0px solid;
        padding: 0px;
        padding-left: 15px;
        padding-right: 35px;
        border-radius: 3px;
        height: 42px;
        font-size: 16px;
        line-height: 42px;
        min-width: 100px;
        width: 100%;
        box-sizing: border-box;
        &::placeholder {
            opacity: 1;
        }
    }
    &.comoo-open {
        .input::placeholder {
            opacity: 0.5;
        }
    }
    .list {
        position: absolute;
        top: 39px;
        color: #29497b;
        left: 0px;
        min-width: 100%;
        background: white;
        border-radius: 3px;
        padding: 5px 0px;
        max-height: 30vh;
        overflow: auto;
        .item {
            cursor: pointer;
            display: block;
            line-height: 1.2em;
            padding: 3px 15px;
            &.selected, &:hover {
                background: #29497b;
                color: white;
            }
        }
    }
}
.combo-open, .combo-open .list {
    box-shadow: 0px 0px 5px #aaaaaa;
}
</style>