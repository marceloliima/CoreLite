<?php

namespace App\Core;

/**
 * Class Formatter
 * 
 * 🔹 Classe utilitária para formatação de dados brasileiros
 * 🇧🇷 Formatação completa para nomes, documentos, telefones, datas, valores monetários e textos
 * 
 * @package App\Core
 * @author Seu Nome
 * @version 2.0
 * 
 * 📋 Características:
 * - Validação e sanitização de entradas
 * - Tratamento de caracteres UTF-8
 * - Retornos consistentes para entradas inválidas
 * - Métodos estáticos para uso fácil
 * - Foco em padrões brasileiros
 * 
 * ⚠️ Observações:
 * - Todas as datas são tratadas no padrão brasileiro (DD/MM/YYYY)
 * - Formatação monetária em Real (R$)
 * - Suporte completo a caracteres especiais portugueses
 */
class Formatter
{
    /**
     * 🔤 Palavras que permanecem minúsculas em nomes próprios
     * 📝 Lista de preposições e conjunções comuns em português
     */
    private const EXCECOES_NOME = [
        'da', 'de', 'do', 'das', 'dos', 'e'
    ];

    /**
     * 🔤 Sufixos que devem permanecer em maiúsculo em nomes
     * 📝 Inclui sufixos comuns em sobrenomes
     */
    private const SUFIXOS_MAIUSCULOS = [
        'filho', 'filha', 'neto', 'neta', 'sobrinho', 'sobrinha', 'junior', 'jr'
    ];

    // ===============================
    // 🔤 FORMATAÇÃO DE TEXTOS E NOMES
    // ===============================

    /**
     * 📝 Formata nome completo com capitalização inteligente
     * 
     * ✨ Características:
     * - Mantém preposições minúsculas
     * - Preserva sufixos em maiúsculo
     * - Remove espaços extras
     * - Suporte a UTF-8
     * 
     * @param string $nome Nome a ser formatado
     * @return string Nome formatado
     * 
     * 📌 Exemplos:
     * - "joão da silva" → "João da Silva"
     * - "MARIA DOS SANTOS FILHA" → "Maria dos Santos FILHA"
     */
    public static function nome(string $nome): string
    {
        if (empty(trim($nome))) {
            return '';
        }

        // Normaliza o nome: remove espaços extras e converte para minúsculo
        $nome = trim(preg_replace('/\s+/', ' ', mb_strtolower($nome, 'UTF-8')));
        $partes = explode(' ', $nome);

        $formatado = array_map(function ($parte) {
            if (in_array($parte, self::EXCECOES_NOME, true)) {
                return $parte; // Mantém preposições minúsculas
            }
            
            if (in_array(mb_strtolower($parte, 'UTF-8'), self::SUFIXOS_MAIUSCULOS)) {
                return mb_strtoupper($parte, 'UTF-8'); // Mantém sufixos em maiúsculo
            }
            
            return mb_convert_case($parte, MB_CASE_TITLE, 'UTF-8');
        }, $partes);

        // Garante que a primeira letra seja maiúscula
        if (!empty($formatado[0])) {
            $formatado[0] = mb_convert_case($formatado[0], MB_CASE_TITLE, 'UTF-8');
        }

        return implode(' ', $formatado);
    }

    /**
     * 📝 Converte texto para formato de título
     * 
     * @param string $texto Texto a ser convertido
     * @return string Texto em formato de título
     */
    public static function titulo(string $texto): string
    {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 📝 Limita texto com elipse inteligente
     * 
     * @param string $texto Texto original
     * @param int $limite Número máximo de caracteres
     * @param string $sufixo Sufixo ao final (padrão: ...)
     * @return string Texto limitado
     */
    public static function limitarTexto(string $texto, int $limite = 100, string $sufixo = '...'): string
    {
        if (mb_strlen($texto, 'UTF-8') <= $limite) {
            return $texto;
        }

        $texto = mb_substr($texto, 0, $limite, 'UTF-8');
        $ultimoEspaco = mb_strrpos($texto, ' ', 0, 'UTF-8');

        if ($ultimoEspaco !== false) {
            $texto = mb_substr($texto, 0, $ultimoEspaco, 'UTF-8');
        }

        return $texto . $sufixo;
    }

    // ===============================
    // 📄 FORMATAÇÃO DE DOCUMENTOS
    // ===============================

    /**
     * 📝 Formata CPF: 12345678901 → 123.456.789-01
     * 
     * @param string $cpf CPF sem formatação
     * @return string CPF formatado ou original se inválido
     */
    public static function cpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return $cpf; // Retorna original se inválido
        }
        
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * 📝 Formata CNPJ: 12345678000199 → 12.345.678/0001-99
     * 
     * @param string $cnpj CNPJ sem formatação
     * @return string CNPJ formatado ou original se inválido
     */
    public static function cnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            return $cnpj; // Retorna original se inválido
        }
        
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * 📝 Formata CPF ou CNPJ automaticamente
     * 
     * @param string $documento Documento sem formatação
     * @return string Documento formatado
     */
    public static function cpfCnpj(string $documento): string
    {
        $documento = preg_replace('/\D/', '', $documento);
        
        return match(strlen($documento)) {
            11 => self::cpf($documento),
            14 => self::cnpj($documento),
            default => $documento
        };
    }

    // ===============================
    // 📞 FORMATAÇÃO DE TELEFONES
    // ===============================

    /**
     * 📝 Formata telefone brasileiro
     * 
     * ✨ Suporta:
     * - Celulares: (11) 99999-9999
     * - Fixos: (11) 4444-4444
     * - Números internacionais
     * 
     * @param string $telefone Número de telefone
     * @return string Telefone formatado
     */
    public static function telefone(string $telefone): string
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $tamanho = strlen($telefone);

        return match($tamanho) {
            11 => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone), // Celular
            10 => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone), // Fixo
            13 => preg_replace('/(\d{2})(\d{2})(\d{5})(\d{4})/', '+$1 ($2) $3-$4', $telefone), // Internacional
            default => $telefone
        };
    }

    // ===============================
    // 📅 FORMATAÇÃO DE DATAS
    // ===============================

    /**
     * 📝 Converte data do formato DB (YYYY-MM-DD) para BR (DD/MM/YYYY)
     * 
     * @param string|null $data Data no formato DB
     * @return string|null Data formatada ou null se inválida
     */
    public static function dataParaBr(?string $data): ?string
    {
        if (empty($data) || $data === '0000-00-00' || $data === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $dt = \DateTime::createFromFormat('Y-m-d', $data);
            return $dt ? $dt->format('d/m/Y') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 📝 Converte data do formato BR (DD/MM/YYYY) para DB (YYYY-MM-DD)
     * 
     * @param string|null $data Data no formato BR
     * @return string|null Data formatada ou null se inválida
     */
    public static function dataParaDb(?string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        try {
            $dt = \DateTime::createFromFormat('d/m/Y', $data);
            return $dt ? $dt->format('Y-m-d') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 📝 Formata datetime para formato brasileiro
     * 
     * @param string|null $datetime DateTime original
     * @param string $formato Formato de saída (padrão: d/m/Y H:i:s)
     * @return string DateTime formatado ou '-' se inválido
     */
    public static function datetime(?string $datetime, string $formato = 'd/m/Y H:i:s'): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            $dt = new \DateTime($datetime);
            return $dt->format($formato);
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * 📝 Converte datetime do formato BR para DB
     * 
     * @param string|null $datetime DateTime no formato BR
     * @param string $formato Formato de entrada (padrão: d/m/Y H:i:s)
     * @return string|null DateTime formatado ou null se inválido
     */
    public static function datetimeParaDb(?string $datetime, string $formato = 'd/m/Y H:i:s'): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            $dt = \DateTime::createFromFormat($formato, $datetime);
            return $dt ? $dt->format('Y-m-d H:i:s') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 📝 Formata data por extenso em português
     * 
     * @param string $data Data no formato YYYY-MM-DD
     * @return string Data por extenso
     */
    public static function dataPorExtenso(string $data): string
    {
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];

        $diasSemana = [
            'domingo', 'segunda-feira', 'terça-feira', 'quarta-feira',
            'quinta-feira', 'sexta-feira', 'sábado'
        ];

        try {
            $dt = new \DateTime($data);
            $diaSemana = $diasSemana[(int)$dt->format('w')];
            $dia = $dt->format('d');
            $mes = $meses[(int)$dt->format('m')];
            $ano = $dt->format('Y');

            return "$diaSemana, $dia de $mes de $ano";
        } catch (\Exception $e) {
            return '-';
        }
    }

    // ===============================
    // 💰 FORMATAÇÃO MONETÁRIA
    // ===============================

    /**
     * 📝 Formata valor monetário para Real brasileiro
     * 
     * @param float $valor Valor a ser formatado
     * @param int $decimais Número de casas decimais
     * @return string Valor formatado (R$ 1.234,56)
     */
    public static function moeda(float $valor, int $decimais = 2): string
    {
        return 'R$ ' . number_format($valor, $decimais, ',', '.');
    }

    /**
     * 📝 Formata valor monetário de string para float
     * 
     * @param string $valor Valor formatado (R$ 1.234,56)
     * @return float Valor numérico (1234.56)
     */
    public static function moedaParaFloat(string $valor): float
    {
        $valor = preg_replace('/[^\d,]/', '', $valor);
        $valor = str_replace(',', '.', str_replace('.', '', $valor));
        return (float) $valor;
    }

    /**
     * 📝 Formata valor por extenso em português
     * 
     * @param float $valor Valor a ser convertido
     * @return string Valor por extenso
     */
    public static function moedaPorExtenso(float $valor): string
    {
        $singular = ['centavo', 'real', 'mil', 'milhão', 'bilhão', 'trilhão'];
        $plural = ['centavos', 'reais', 'mil', 'milhões', 'bilhões', 'trilhões'];
        
        // Implementação simplificada - para produção use uma biblioteca dedicada
        $valorInteiro = (int) $valor;
        $valorCentavos = round(($valor - $valorInteiro) * 100);
        
        $extenso = [];
        
        if ($valorInteiro > 0) {
            $extenso[] = number_format($valorInteiro, 0, '', '.') . ' ' . 
                        ($valorInteiro === 1 ? $singular[1] : $plural[1]);
        }
        
        if ($valorCentavos > 0) {
            $extenso[] = $valorCentavos . ' ' . 
                        ($valorCentavos === 1 ? $singular[0] : $plural[0]);
        }
        
        return implode(' e ', $extenso) ?: 'zero reais';
    }

    // ===============================
    // 🎭 MÁSCARAS E FORMATAÇÕES GENÉRICAS
    // ===============================

    /**
     * 📝 Aplica máscara personalizada a um valor
     * 
     * @param string $valor Valor original
     * @param string $mascara Máscara a ser aplicada (# = dígito)
     * @return string Valor com máscara aplicada
     * 
     * 📌 Exemplos:
     * - mask('12345678901', '###.###.###-##') → 123.456.789-01
     * - mask('12345678901234', '##.###.###/####-##') → 12.345.678/9012-34
     */
    public static function mascara(string $valor, string $mascara): string
    {
        $valor = preg_replace('/\D/', '', $valor);
        $mascarado = '';
        $posicao = 0;

        for ($i = 0; $i < strlen($mascara); $i++) {
            if ($mascara[$i] === '#') {
                $mascarado .= $valor[$posicao] ?? '';
                $posicao++;
            } else {
                $mascarado .= $mascara[$i];
            }
            
            // Para se acabaram os dígitos
            if ($posicao >= strlen($valor)) {
                break;
            }
        }

        return $mascarado;
    }

    /**
     * 📝 Remove toda a formatação, deixando apenas números
     * 
     * @param string $valor Valor formatado
     * @return string Apenas números
     */
    public static function apenasNumeros(string $valor): string
    {
        return preg_replace('/\D/', '', $valor);
    }

    /**
     * 📝 Formata CEP: 12345678 → 12345-678
     * 
     * @param string $cep CEP sem formatação
     * @return string CEP formatado
     */
    public static function cep(string $cep): string
    {
        $cep = preg_replace('/\D/', '', $cep);
        
        if (strlen($cep) === 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
        }
        
        return $cep;
    }

    // ===============================
    // 🔧 UTILITÁRIOS
    // ===============================

    /**
     * 📝 Valida se um CPF é válido
     * 
     * @param string $cpf CPF a ser validado
     * @return bool True se for válido
     */
    public static function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Implementação completa da validação de CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * 📝 Valida se um CNPJ é válido
     * 
     * @param string $cnpj CNPJ a ser validado
     * @return bool True se for válido
     */
    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Implementação completa da validação de CNPJ
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        if ($cnpj[12] != $digito1) {
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        return $cnpj[13] == $digito2;
    }

    /**
     * 📝 Sanitiza string removendo caracteres especiais perigosos
     * 
     * @param string $dados String a ser sanitizada
     * @return string String sanitizada
     */
    public static function sanitizar(string $dados): string
    {
        return htmlspecialchars($dados, ENT_QUOTES, 'UTF-8');
    }
}