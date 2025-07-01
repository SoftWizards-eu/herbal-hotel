<template>
    <div :class="'table' + (this.rowActions ? ' table-with-row-actions' : ' table-without-row-actions')" @click.prevent="">
        <div class="table-header">
            <div class="col checkbox-col" v-if="multiple">
                <input type="checkbox" disabled />
            </div>
            <template v-for="(col, index) in columns">
                <span class="space" v-if="index > (multiple ? -1 : 0)" :key="index + 'space'"></span>
                <div :key="index" class="col" :style="colStyle(index)" @click="sort(index)">
                    {{ col.abbrev ? col.abbrev : col.title }}
                    <template v-if="model.orderBy == col.data">
                        <i class="icon-arrow-down3" v-if="model.desc"></i>
                        <i class="icon-arrow-up3" v-else></i>
                    </template>
                </div>
            </template>
        </div>
        <div class="table-header table-header-search">
            <div class="col checkbox-col" v-if="multiple">
                
            </div> 
            <template v-for="(col, index) in columns">
                <span class="space" v-if="index  > (multiple ? -1 : 0)" :key="index + 'space'"></span>
                <div :key="index" :class="'col '" :style="colStyle(index)">
                    <span v-if="!col.filter" class="no-filter"></span>
                    <slot v-else :name="'filter-' + col.data.toLowerCase()" :filter="filter">
                        <input type="text" placeholder="..." class="filter" v-model="model[col.data]" @keyup.enter.stop="load()" />
                    </slot>
                </div>
                
            </template>
        </div>
        <div class="table-body">
            <template v-for="(row, index) in results.data">
                <div class="table-row" :key="index" :style="[rowColor(row,index) ? {'background': rowColor(row,index)} : {}]" @click.prevent.stop="rowClick(row, $event)" @dblclick.prevent.stop="rowDblClick(row)">
                    <div class="table-columns">
                        <div class="col checkbox-col" v-if="multiple">
                            <input type="checkbox" :checked="selection.includes(row)" @click.stop="rowClick(row, $event)" />
                        </div>
                        <template v-for="(col, colx) in colIds">
                            <span class="space" v-if="colx > (multiple ? -1 : 0)" :key="colx + 's' + index"></span>
                            <div class="col" :key="colx + ':' + index" :style="colStyle(colx)">
                                <slot :name="col.toLowerCase()" :col="col" :row="row" :value="row[col]">
                                    {{ row[col] }}
                                </slot>
                            </div>
                        </template>
                        <span class="space" v-if="rowActions"></span>
                        <div :class="'col row-actions'"  v-if="rowActions" @click.stop="">
                            <slot name="row-actions" :row="row"></slot>
                        </div>
                    </div>
                    <div class="table-rest"><slot name="expanded" :row="row"></slot></div>
                    <div class="table-actions" :key="'act' + index" v-if="actions && selection.includes(row)" @click.prevent.stop="">
                        <slot name="actions" :row="row"></slot>
                    </div>
                    <div class="table-actions overlap" :key="'act' + index" v-if="hoverActions" @click.prevent.stop="">
                        <slot name="hover-actions" :row="row"></slot>
                    </div>
                </div>
                
            </template>
            <div  v-if="results.pageNo > 0 && results.data.length == 0">
                <br />
                <div class="alert">
                    Lista jest pusta
                
                </div>
            </div>
        </div>
        <div class="pagination" v-show="results.pageNo ==0 || results.data.length > 0">
            <span class="prev button" v-if="results.pageNo > 1" @click="goPage(results.pageNo - 1)">
                {{$t('pagging.prev')}}
            </span>
            <p v-if="results.pageNo > 0">{{ $t('pagging.showing') }} {{results.pageSize}} {{$t('pagging.from')}} {{ results.total }}. {{$t('pagging.current')}} {{model.pageNo}} / {{pages}} </p>
            <p v-else>{{$t('app.loading')}}</p>
            <span class="next button" v-if="hasMore"  @click="goPage(results.pageNo + 1)">
                {{$t('pagging.next')}}
            </span>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        func: {
            type: Function
        },
        rowColor: {
            type: Function,
            default: () => null
        },
        url : String,
        columns: Array,
        filters: Object,
        search: null,
        actions: {
            default: true
        },
        hoverActions: {
            default: false
        },
        rowActions: {
            default: false
        },
        select: {
            type: Boolean,
            default: false
        },
        multiple: {
            type: Boolean,
            default: false
        },
        orderBy: {
            type: String,
            default: null
        },
        desc: {
            type: Boolean,
            default: false
        }
    },
    data() {
        let ids = []
        this.columns.forEach(c => ids.push(c.data))
        let filters = this.filters||{}, defs = {
                orderBy : this.orderBy,
                desc :this.desc,
                pageNo : 1,
                pageSize: 50
        };
       
        return {
            colIds : ids,
            model : {...defs, ...filters},
            
            selection : [],
            results : {
                data : [],
                pageNo : 0,
                total: 0,
                pageSize : filters.pageSize||50
            },
            loading: false
        }
    },
    computed: {
        pages() {
            return Math.ceil(this.results.total / this.results.pageSize)
        },
        hasMore() {
            if (this.results.pageNo == 0) {
                return false
            }
            return this.pages > this.results.pageNo 
        }
    },
    mounted() {
        this.load()
    },
    methods: {
        refresh() {
            this.load()
        },
        filter(filters) {
            this.model = {...this.model, ...filters}
            this.model.pageNo = 1
            this.load()
        },
        colStyle(idx) {
            let style = ''
            if (idx+1 == this.columns.length) {
                if (this.rowActions) {
                    style = 'flex: 1;'
                }
            } else {
                style = 'width:' + this.columns[idx].width + 'px;'
            }
            if (this.columns[idx].align) {
                style += ' text-align: ' + this.columns[idx].align + ';';
            }

            return style
        },
        load() {
            return (this.func || this.defaultFunc)(this.model).then(r => {
                this.results = r
                this.selection = []
            })
        },
        defaultFunc(model) {
            return this.$http.get(this.url, {
                params: model
            }).then(resp => {
                return resp.data
            });
        },
        sort(id) {
            if (!this.columns[id].sortable) {
                return
            }
            let data = this.columns[id].data
            if (this.model.orderBy == data) {
                this.model.desc = !this.model.desc
            } else {
                this.model.orderBy = data
                this.model.desc = false
            }
            this.load()
        },
        goPage(page) {
            this.model.pageNo = page
            this.load()
        },
        rowClick(item, evt) {
            if (this.select) {
                if (this.selection.includes(item) && this.multiple) {
                    this.selection.splice(this.selection.indexOf(item), 1)
                } else if(this.multiple) {
                    if (evt.shiftKey && this.selection.length > 0) {
                        document.getSelection().removeAllRanges();
                        let i = this.results.data.indexOf(this.selection[this.selection.length - 1])
                        let j = this.results.data.indexOf(item)
                        this.selection = []
                        if (j < i) [j, i] = [i, j]
                        for (let k = i; k <= j; k++)
                            this.selection.push(this.results.data[k])
                    }
                    else {
                        this.selection.push(item)
                    }
                } else {
                    this.selection = [item]
                }
                this.$emit('selection', this.selection)
            } else {
                this.$emit('row-click', item)
            }
            
        },
        rowDblClick(item) {
            this.$emit('row-dblclick', item)
        }
    }
}
</script>
<style lang="scss">
@import '~/style/table.scss';
</style>
