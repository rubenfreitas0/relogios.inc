export const sleep = (ms = 0) => {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

export const validators = {
  email: (v: string) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return pattern.test(v) || 'Por favor, introduza um endereço de email válido'
  },
  required: (v: any) => !!v || 'Este campo é obrigatório',
}
