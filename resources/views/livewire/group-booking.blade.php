<div>
    <div class="bg-primary w-full py-16 px-6 lg:px-0">
        <div class="w-full max-w-screen-xl items-center grid lg:grid-cols-5 gap-8 lg:gap-32 mx-auto text-white">
            <div class="lg:p-8 rounded-lg w-full mx-auto space-y-8 lg:col-span-2 overflow-x-hidden">
                <div class="space-y-2 max-w-xs">
                    <h2 class="text-2xl font-bold mb-2">Book For A Group?</h2>
                    <p class="mb-6">Fill the form to receive bids from the top hotels near you:</p>
                </div>

                <div class="lg:space-y-8 relative flex overflow-x-hidden lg:flex-col gap-2 max-w-max mx-auto lg:mx-0">
                    <div
                        class="absolute -z-0 lg:left-4 lg:top-8 left-0 lg:bottom-4 h-2px w-[2px] bg-cool-300 transform lg:-translate-x-1/2 -translate-y-1/2 lg:translate-y-0">
                    </div>
                    <div
                        class="absolute -z-0
             left-10 right-10 sm:inset-x-14 top-4 h-[2px] w-auto -translate-y-1/2
             bg-cool-300 transform
             lg:hidden">
                    </div>

                    <div x-data="{
                        currentStep: 1,
                        steps: [
                            { id: 1, label: 'Room Information', icon: 'teenyicons:home-outline' },
                            { id: 2, label: 'Hall Information' },
                            { id: 3, label: 'Meal Information' },
                            { id: 4, label: 'Hotel Information' }
                        ]
                    }">
                        <div
                            class="flex flex-col lg:flex-row justify-center gap-4 lg:justify-normal items-center lg:space-x-4">
                            <div x-for="(step, index) in steps" :key="index" class="cursor-pointer"
                                @click="currentStep = step.id">
                                <div class="w-8 h-8 z-20 flex items-center justify-center rounded-full border-2 transition-all"
                                    :class="{
                                        'border-white bg-white text-primary-700': currentStep === step.id,
                                        'border-cool-300 bg-primary text-cool-300': currentStep !== step.id
                                    }">
                                    <i x-if="step.icon" :class="step.icon" class="w-4 h-4 mx-auto block"
                                        :class="{ 'text-primary-700': currentStep === step.id }"></i>
                                    <span x-if="!step.icon" class="block" x-text="step.id"></span>
                                </div>
                                <span class="transition-all text-center block lg:text-left mx-auto"
                                    :class="{
                                        'text-white font-semibold': currentStep === step.id,
                                        'text-cool-300': currentStep !== step.id
                                    }"
                                    x-text="step.label">

                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <UForm :schema="schema" :state="state"
                class="lg:col-span-3 bg-white p-12 text-cool-900 lg:max-w-max">
                <div x-if="currentStep === 1" class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <div class="w-full lg:col-span-2">
                        <Button @click="currentStep = 2" type="button"
                            class="w-full rounded-none block text-center">Save and
                            Continue</Button>
                    </div>
                </div>

                <!-- step 2 -->
                <div class="w-full" v-if="currentStep === 2">
                    <div class="space-y-4">
                        <h1 class="text-xl font-medium">Will they require a hall?</h1>
                        <span class="small">This information is require in case you want to also book hall for the
                            group</span>

                        <div class="pt-3 flex gap-4">
                            <URadio v-for="method in hallOptions" :key="method" v-model="state.hall"
                                :value="method" :label="method" class="capitalize cursor-pointer" />
                        </div>
                        <div class="col:col-span-2 grid grid-cols-2 gap-6 pt-4" v-if="state.hall == 'yes'">
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-8">
                            <Button class="w-full rounded-none block text-center" @click="currentStep = 1">
                                Go Back</Button>
                            <Button class="w-full rounded-none block text-center" @click="currentStep = 3">Save and
                                Continue</Button>
                        </div>
                    </div>
                </div>

                <!-- step 3 -->
                <div class="w-full" v-if="currentStep === 3">
                    <div class="space-y-4">
                        <h1 class="text-xl font-medium">Will they require a meal?</h1>
                        <span class="small">This information is require in case you want to also inlude feeding</span>

                        <div class="pt-3 flex gap-4">
                            <URadio v-for="method in mealOptions" :key="method" v-model="state.meal"
                                :value="method" :label="method" class="capitalize cursor-pointer" />
                        </div>
                        <div class="col:col-span-2 grid grid-cols-2 gap-6 pt-4" v-if="state.meal == 'yes'">
                            <div class="flex space-x-4 col-span-2">
                                <Button @click="state.mealOptions.push(meal)" size="lg"
                                    class="rounded-none capitalize"></Button>
                            </div>

                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-8">
                            <Button label="" class="w-full rounded-none block text-center"
                                @click="currentStep = 2">Go Back</Button>
                            <Button class="w-full rounded-none block text-center" @click="currentStep = 4"></Button>
                        </div>
                    </div>
                </div>

                <!-- step 4 -->
                <div class="w-full" v-if="currentStep === 4">
                    <div class="space-y-4">
                        <h1 class="text-lg font-medium">Select atleast 3 hotels</h1>
                        <USelectMenu option-attribute="name" value-attribute="id" v-model="state.roomCategory"
                            :options="roomCategories" placeholder="Search hotels" />
                        <div class="col:col-span-2">
                            <div v-if="state.mealOptions.length" class="flex space-x-4 col-span-2">
                                <Button v-for="meal in state.hotelOptions" :key="meal"
                                    @click="state.mealOptions.push(meal)" :label="meal" size="lg"
                                    class="rounded-none capitalize" />
                            </div>
                        </div>
                        <div class="flex flex-col grid-cols-2 gap-4">
                            <div class="max-w-[300px]">
                                <h2 class="font-medium text-lg">Do you other hotels can send in bids as well?</h2>
                                <span class="">If you select yes, you will also received bid from other hotels
                                    you did not
                                    select.</span>
                            </div>
                            <div class="pt-3 flex gap-4">
                                <URadio v-for="method in bidOptions" :key="method" v-model="state.hotel"
                                    :value="method" :label="method" class="capitalize cursor-pointer" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-8">
                            <Button label="Go Back" class="w-full rounded-none block text-center"
                                @click="currentStep = 3" />
                            <Button type="submit" label="Save and Continue"
                                class="w-full rounded-none block text-center" />
                        </div>
                    </div>
                </div>
            </UForm>
        </div>
    </div>
</div>
