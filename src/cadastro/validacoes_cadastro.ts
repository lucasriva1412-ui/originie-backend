
export function limparCpf(cpf: string): string {
  return cpf.replace(/\D/g, "");
}

export function validarCpf(valor: string): boolean {
  const cpf = limparCpf(valor);

  if (!/^\d{11}$/.test(cpf) || /^(\d)\1{10}$/.test(cpf)) {
    return false;
  }

  for (let tamanho = 9; tamanho <= 10; tamanho++) {
    let soma = 0;

    for (let i = 0; i < tamanho; i++) {
      soma += Number(cpf[i]) * (tamanho + 1 - i);
    }

    const resto = (soma * 10) % 11;
    const digito = resto === 10 ? 0 : resto;

    if (digito !== Number(cpf[tamanho])) {
      return false;
    }
  }

  return true;
}

export function validarEmail(email: string): boolean {
  if (email.length === 0 || email.length > 150) {
    return false;
  }

  return /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/.test(email);
}

export function avaliarSenha(senha: string): {
  nivel: "fraca" | "media" | "forte";
  valida: boolean;
  mensagem: string;
} {
  if (Buffer.byteLength(senha, "utf8") > 72) {
    return {
      nivel: "fraca",
      valida: false,
      mensagem: "A senha não pode ultrapassar 72 bytes."
    };
  }

  if (senha.length < 8) {
    return {
      nivel: "fraca",
      valida: false,
      mensagem: "A senha deve ter pelo menos 8 caracteres."
    };
  }

  const temMaiuscula = /[A-Z]/.test(senha);
  const temMinuscula = /[a-z]/.test(senha);
  const temNumero = /[0-9]/.test(senha);
  const temEspecial = /[^A-Za-z0-9]/.test(senha);

  if (
    !temMaiuscula ||
    !temMinuscula ||
    !temNumero ||
    !temEspecial
  ) {
    return {
      nivel: "fraca",
      valida: false,
      mensagem:
        "A senha precisa conter letras maiúsculas, minúsculas, números e caracteres especiais."
    };
  }

  if (senha.length >= 12) {
    return {
      nivel: "forte",
      valida: true,
      mensagem: "Senha forte."
    };
  }

  return {
    nivel: "media",
    valida: true,
    mensagem: "Senha de nível médio."
  };
}
