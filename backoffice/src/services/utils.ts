export const sleep = (ms = 0) => {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

/** Validação de formulários */
export const validators = {
  email: (v: string) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return pattern.test(v) || 'Introduz um email válido'
  },
  required: (v: any) => !!v || 'Este campo é obrigatório',
}
