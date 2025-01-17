<template>
    <div class="container">
        <div class="head">
            <!-- <a href="/coin" class="nav-button w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-300 text-base font-medium text-black hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                Coin List
            </a> -->
            <div class="coin-settings">
                <div class="coin-filter">
                    <div class="selected-coins flex">
                        <div v-for="(coinItem, key) in selectedCoins">
                            <img @click="removeCoin(coinItem)" class="coin h-10 w-10 rounded-full"
                                    :style="'right: -' + (20 * key) + 'px; z-index:' + (100 - key)" :src="coinItem.iconUrl" alt="">
                        </div>
                    </div>

                    <Search :class="'select-coin'" :list="filterableCoinsList" @add-coin="addCoin"/>
                </div>
                <div class="coin-grouping">
                    <v-switch label="Grouped" color="primary" id="grouped" type="checkbox"
                              :checked="grouped"
                              @click="changeGrouped()">
                        Grouped
                    </v-switch>
                </div>
            </div>
        </div>

        <div class="chart-filters">
            <div class="filter-range">
                <FilterRange v-for="rangeItem in filterRangeList" :value="rangeItem" :currentValue="filterRange" @changeRange="changeRange($event)" />
            </div>
        </div>

        <PriceChart :chartDates="chartDates"
                    :chartPrices="chartPrices"
                    :filterRange="filterRange"
        />
        <div class="mt-16">
            <CommitsChart v-show="chartGithubCommitsDates[0]?.length"
                          :chartGithubCommits="chartGithubCommits"
                          :chartGithubCommitsDates="chartGithubCommitsDates"

            />
        </div>

        <p v-if="error" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
            {{ error }}
        </p>
    </div>
</template>

<script>
    import "../../css/charts.scss";
    import { defineComponent, computed, reactive, toRefs } from 'vue';
    import { LineChart } from 'vue-chart-3';
    import FilterRange from '../Components/FilterRange/FilterRange.vue';
    import FilterCoin from '../Components/FilterCoin/FilterCoin.vue';
    import PriceChart from '../Components/PriceChart/PriceChart.vue';
    import CommitsChart from '../Components/CommitsChart/CommitsChart.vue';
    import Search from '../Components/SearchCoin/SearchCoin.vue';
    import { Inertia } from '@inertiajs/inertia';
    import { usePage } from '@inertiajs/inertia-vue3'
    import 'chartjs-adapter-moment';
    import 'vuetify/dist/vuetify.min.css'

    let PALETTE = ['#f3a683', '#f7d794', '#778beb', '#e77f67', '#cf6a87', '#786fa6', '#f8a5c2', '#63cdda', '#ea8685']

    export default defineComponent({
        components: { LineChart, FilterRange, FilterCoin, PriceChart, CommitsChart, Search },

        props: {
            chartPrices: Array,
            chartDates: Array,
            error: String,
            coinsList: Array,
            filterRangeList: Array,
            grouped: Boolean,
            chartGithubCommits: Array,
            chartGithubCommitsDates: Array
        },

        setup(props) {
            const state = reactive({
                filterRange: '3m',
                selectedCoins: usePage().props.value.coinsSelected,
                grouped: props.grouped
            });

            const filterableCoinsList = computed(() => {
                return props.coinsList.filter((coin) => {
                    let isPresent = false
                    state.selectedCoins.forEach((current) => {
                        if(current.symbol == coin.symbol) isPresent = true
                    });
                    return !isPresent
                })
            })

            function changeRange(range) {
                state.filterRange = range;
                this.updateChart();
            }

            function addCoin(coin)
            {
                state.selectedCoins.push(coin);
                updateChart();
            }

            function removeCoin(removeCoin)
            {
                state.selectedCoins = state.selectedCoins.filter((coin) => (
                    coin.symbol != removeCoin.symbol
                ));
                updateChart();
            }

            function changeGrouped()
            {
                state.grouped = !state.grouped
                updateChart()
            }

            function updateChart() {
                Inertia.post('/', {
                    'coins': state.selectedCoins,
                    'range': state.filterRange,
                    'grouped': state.grouped
                })
            }

            Object.map = function (obj, fn, ctx) {
                return Object.keys(obj).reduce((a, b) => {
                    a[b] = fn.call(ctx || null, b, obj[b]);
                    return a;
                }, {});
            };

            return {
                changeRange,
                changeGrouped,
                updateChart,
                ...toRefs(state),
                addCoin,
                removeCoin,
                filterableCoinsList,
            };
        },
    });
</script>
