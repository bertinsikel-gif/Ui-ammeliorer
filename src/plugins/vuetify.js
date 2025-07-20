import { createVuetify } from 'vuetify'
import 'vuetify/styles'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

// Thème Material Design 3 personnalisé pour UCB
const ucbTheme = {
  dark: false,
  colors: {
    primary: '#6750A4',
    'primary-darken-1': '#21005D',
    secondary: '#625B71',
    'secondary-darken-1': '#1D192B',
    accent: '#7C4DFF',
    error: '#BA1A1A',
    info: '#2196F3',
    success: '#4CAF50',
    warning: '#FB8C00',
    background: '#FEF7FF',
    surface: '#FFFFFF',
    'surface-variant': '#E7E0EC',
    'on-surface': '#1D1B20',
    'on-surface-variant': '#49454F',
    'primary-container': '#EADDFF',
    'on-primary-container': '#21005D',
    'secondary-container': '#E8DEF8',
    'on-secondary-container': '#1D192B',
    'error-container': '#FFDAD6',
    'on-error-container': '#410002',
    outline: '#79747E',
    'outline-variant': '#CAC4D0'
  }
}

export default createVuetify({
  theme: {
    defaultTheme: 'ucbTheme',
    themes: {
      ucbTheme
    }
  },
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: {
      mdi
    }
  },
  defaults: {
    VBtn: {
      style: 'text-transform: none;'
    },
    VCard: {
      elevation: 2
    }
  }
})