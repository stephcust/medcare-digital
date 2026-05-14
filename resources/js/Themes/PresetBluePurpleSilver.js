import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

/** @type {typeof Aura} */
export const PresetBluePurpleSilver = definePreset(Aura, {
    semantic: {
        primary: {
            50: 'rgb(var(--primary-50))',
            100: 'rgb(var(--primary-100))',
            200: 'rgb(var(--primary-200))',
            300: 'rgb(var(--primary-300))',
            400: 'rgb(var(--primary-400))',
            500: 'rgb(var(--primary-500))',
            600: 'rgb(var(--primary-600))',
            700: 'rgb(var(--primary-700))',
            800: 'rgb(var(--primary-800))',
            900: 'rgb(var(--primary-900))',
            950: 'rgb(var(--primary-950))',
        },
        secondary: {
            50: 'rgb(var(--secondary-50))',
            100: 'rgb(var(--secondary-100))',
            200: 'rgb(var(--secondary-200))',
            300: 'rgb(var(--secondary-300))',
            400: 'rgb(var(--secondary-400))',
            500: 'rgb(var(--secondary-500))',
            600: 'rgb(var(--secondary-600))',
            700: 'rgb(var(--secondary-700))',
            800: 'rgb(var(--secondary-800))',
            900: 'rgb(var(--secondary-900))',
            950: 'rgb(var(--secondary-950))',
        },
        tertiary: {
            50: 'rgb(var(--tertiary-50))',
            100: 'rgb(var(--tertiary-100))',
            200: 'rgb(var(--tertiary-200))',
            300: 'rgb(var(--tertiary-300))',
            400: 'rgb(var(--tertiary-400))',
            500: 'rgb(var(--tertiary-500))',
            600: 'rgb(var(--tertiary-600))',
            700: 'rgb(var(--tertiary-700))',
            800: 'rgb(var(--tertiary-800))',
            900: 'rgb(var(--tertiary-900))',
            950: 'rgb(var(--tertiary-950))',
        },
        colorScheme: {
            light: {
                secondary: {
                    color: '{secondary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{secondary.600}',
                    activeColor: '{secondary.700}'
                },
                tertiary: {
                    color: '{tertiary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{tertiary.600}',
                    activeColor: '{tertiary.700}'
                },
            }
        }
    },
    components: {
        button: {
            colorScheme: {
                light: {
                    root: {
                        secondary: {
                            background: '{secondary.color}',
                            hoverBackground: '{secondary.hover.color}',
                            activeBackground: '{secondary.active.color}',
                            borderColor: '{secondary.color}',
                            hoverBorderColor: '{secondary.hover.color}',
                            activeBorderColor: '{secondary.active.color}',
                            color: '{secondary.contrast.color}',
                            hoverColor: '{secondary.contrast.color}',
                            activeColor: '{secondary.contrast.color}',
                            focusRing: {
                                color: '{secondary.color}',
                                shadow: 'none'
                            }
                        },
                        tertiary: {
                            background: '{tertiary.color}',
                            hoverBackground: '{tertiary.hover.color}',
                            activeBackground: '{tertiary.active.color}',
                            borderColor: '{tertiary.color}',
                            hoverBorderColor: '{tertiary.hover.color}',
                            activeBorderColor: '{tertiary.active.color}',
                            color: '{tertiary.contrast.color}',
                            hoverColor: '{tertiary.contrast.color}',
                            activeColor: '{tertiary.contrast.color}',
                            focusRing: {
                                color: '{tertiary.color}',
                                shadow: 'none'
                            }
                        },
                    }
                }
            }
        }
    }
});
