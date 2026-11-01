
import {
  limparCpf,
  validarCpf,
  validarEmail
} from "./validacoes_cadastro";

export class UsuarioCadastro {
  private nome: string = "";
  private cpf: string = "";
  private email: string = "";

  constructor(nome: string, cpf: string, email: string) {
    this.setNome(nome);
    this.setCpf(cpf);
    this.setEmail(email);
  }

  public setNome(nome: string): void {
    if (typeof nome !== "string") {
      throw new Error("Nome inválido.");
    }

    const nomeLimpo = nome.trim();

    if (nomeLimpo.length < 3 || nomeLimpo.length > 100) {
      throw new Error("O nome deve ter entre 3 e 100 caracteres.");
    }

    this.nome = nomeLimpo;
  }

  public setCpf(cpf: string): void {
    if (typeof cpf !== "string") {
      throw new Error("CPF inválido.");
    }

    const cpfLimpo = limparCpf(cpf);

    if (!validarCpf(cpfLimpo)) {
      throw new Error("CPF inválido.");
    }

    this.cpf = cpfLimpo;
  }

  public setEmail(email: string): void {
    if (typeof email !== "string") {
      throw new Error("E-mail inválido.");
    }

    const emailLimpo = email.trim().toLowerCase();

    if (!validarEmail(emailLimpo)) {
      throw new Error("E-mail inválido.");
    }

    this.email = emailLimpo;
  }

  public getNome(): string {
    return this.nome;
  }

  public getCpf(): string {
    return this.cpf;
  }

  public getEmail(): string {
    return this.email;
  }

  public get dadosParaCadastro(): {
    nome: string;
    cpf: string;
    email: string;
  } {
    return {
      nome: this.nome,
      cpf: this.cpf,
      email: this.email
    };
  }
}
