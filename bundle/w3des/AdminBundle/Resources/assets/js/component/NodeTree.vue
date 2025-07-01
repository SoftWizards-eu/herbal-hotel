<template>
    <div>
        <sl-vue-tree ref="slVueTree" :value="tree" @input="onInputHandler" style="position: relative;">
            <template slot="title" slot-scope="{ node }">
              <span class="item-icon">
                
              </span>
              <span @click.prevent.stop="">
                <a :href="node.data.edit">{{ node.title }}</a>
              </span>
              
            </template>
            <template slot="toggle" slot-scope="{ node }">
              <span class="item-icon">
                <i class="icon-arrow-down3" v-if="node.isExpanded"></i>
                <i class="icon-arrow-right3" v-else></i>
              </span>
            </template>
            <template slot="sidebar" slot-scope="{ node }">
                <template v-if="!node.isLeaf">
                    <a :href="node.data.add" v-if="embed.length == 0" class="ico-link" v-tooltip="'dodaj'"><i class="icon-addfile"></i></a>
                    <template v-else>
                        <embed-node-button @selected="selectedEmbed(node.data.embed, $event)" :embed="embed" class="ico-link" v-tooltip="'dodaj'"><i class="icon-addfile"></i></embed-node-button>
                        <embed-node-button @selected="selectedEmbed(node.data.link, $event)" :embed="embed" class="ico-link" v-tooltip="'powiąż'"><i class="icon-link1"></i></embed-node-button>
                    </template>
                    
                    
                </template>
                <a :href="node.data.edit" class="ico-link" v-tooltip="'edytuj'"><i class="icon-edit1"></i></a>
                <confirm-link :href="node.data.remove" v-if="node.children.length == 0"><span class="ico-link" v-tooltip="'usuń'"><i class="icon-delete1"></i></span></confirm-link>
            </template>
            <template slot="draginfo">
                <template v-if="$refs.slVueTree && $refs.slVueTree.getSelected().length">{{ $refs.slVueTree.getSelected()[0].title }}</template>
            </template>
        </sl-vue-tree>
        <nav class="breadcrumbs button-row sticky-bottom">
            <button class="button" @click="onSave">Zapisz kolejność <i class="icon-save"></i></button>
        </nav>
    </div>
</template>
<script>

import SlVueTree from 'sl-vue-tree/dist/sl-vue-tree.js'
import 'sl-vue-tree/dist/sl-vue-tree-minimal.css'
export default {
    props: {
        nodes: {
            type: Array,
            default() {
                return []
            }
        },
        maxDepth: {
            type: Number,
            default: 1
        },
        embed: {
            type: Array,
            default: null
        }
    },
    components: {SlVueTree},
    data() {
        return {
            tree:  this.cloneDeep(this.checkMax(this.nodes)),
            prevNodes:  this.cloneDeep(this.nodes)
        }
    },
    methods: {
        selectedEmbed(link, type) {
            window.location = link.replaceAll('__type__', type)
        },
        onInputHandler(newNodes) {
            let slVueTree = this.$refs.slVueTree;
            let limitReached = false;
            
            slVueTree.traverse((node) => {
              if (node.level > this.maxDepth) {
                limitReached = true;
                return false 
              }
            });
            if (limitReached) {
              this.tree = this.cloneDeep(this.prevNodes);
              return;
            }
            this.checkMax(newNodes)
            this.prevNodes = this.cloneDeep(newNodes);
            this.tree = newNodes
      },
      checkMax(list, lvl) {
        lvl = lvl || 1
        list.forEach(node => {
            if (lvl >= this.maxDepth) {
                node.isLeaf = true
            }
            this.checkMax(node.children, lvl+1)
            
        })
        return list
      },
      cloneDeep(obj) {
        return JSON.parse(JSON.stringify(obj))
      },
      onSave() {
        let map = {}
        this.collect(this.tree, map)
        this.$http.post(window.location.toString(), {
            tree : map
        }).then(resp => {
            window.location = window.location.toString()
        })
      },
      collect(list, mapper, parent) {
        let pos = 0
        list.forEach(node => {
            mapper[node.data.id] = {
                pos : pos++,
                parent: parent || null
            }
            this.collect(node.children, mapper, node.data.id)
        })
        
      }
    }
}
</script>
<style lang="scss">
.sl-vue-tree-node-item  {
    margin: 0px;
    &:hover {
        background: transparent;
    }
}
.sl-vue-tree-node {
    padding-top: 2px;
    padding-bottom: 2px;
}
.sl-vue-tree-node-item:hover {
    cursor: move;
}
.sl-vue-tree-title {
    cursor: pointer !important;
}
.sl-vue-tree-title, .sl-vue-tree-sidebar {
    padding: 5px;
    background: white;
    
}
.sl-vue-tree-title {
    flex: 1;
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
    padding-left: 20px;
}
.sl-vue-tree-sidebar {
    border-top-right-radius: 5px;
    padding-right: 15px;
    border-bottom-right-radius: 5px;
}
.sl-vue-tree-node {
    .item-icon {
        font-size: 12px;
        margin-right: 8px;
    }
}
.sl-vue-tree-cursor {
    border-color: #ff3b3d;
}
.sl-vue-tree-selected > .sl-vue-tree-node-item {
    background: transparent;
}
.sl-vue-tree-selected > .sl-vue-tree-node-item > .sl-vue-tree-title {
    font-weight: bold;
}
.sl-vue-tree-cursor {
    z-index: 30;
    height: 2px;
}
.sl-vue-tree-root {
    padding-left: 0px !important;
}
.sl-vue-tree {
    padding-left: 20px;
    padding-top: 2px;
}
.sl-vue-tree-gap {
    display: none;
}
</style>
