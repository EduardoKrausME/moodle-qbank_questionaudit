<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Brazilian Portuguese strings for qbank_questionaudit.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Auditoria de questões';
$string['auditquestion'] = 'Auditar questão';
$string['auditselected'] = 'Auditar questões selecionadas';
$string['auditcategory'] = 'Auditar esta categoria';
$string['auditresults'] = 'Resultados da auditoria de questões';
$string['auditresultsfor'] = 'Auditoria: {$a}';
$string['backtoquestionbank'] = 'Voltar ao banco de questões';
$string['openquestion'] = 'Abrir editor da questão';
$string['summary'] = 'Resumo';
$string['findings'] = 'Problemas encontrados';
$string['nofindings'] = 'Nenhum problema foi detectado pelas verificações disponíveis.';
$string['severity'] = 'Severidade';
$string['category'] = 'Categoria';
$string['confidence'] = 'Confiança';
$string['evidence'] = 'Trecho afetado';
$string['explanation'] = 'Justificativa';
$string['suggestion'] = 'Sugestão';
$string['source'] = 'Origem';
$string['source_local'] = 'Verificação determinística';
$string['source_ai'] = 'Análise por IA';
$string['severity_error'] = 'Erro';
$string['severity_warning'] = 'Aviso';
$string['severity_suggestion'] = 'Sugestão';
$string['severity_informational'] = 'Informativo';
$string['confidence_low'] = 'Baixa';
$string['confidence_medium'] = 'Média';
$string['confidence_high'] = 'Alta';
$string['ailimitation'] = 'Os apontamentos da IA são hipóteses baseadas no conteúdo da questão. Eles precisam ' .
    'de revisão humana e nunca são aplicados automaticamente.';
$string['bridgeerror'] = 'A auditoria determinística foi concluída, mas a análise por IA não pôde ser executada: {$a}';
$string['bridgeunavailable'] = 'A API obrigatória local_ai_bridge não está disponível.';
$string['unsupportedqtype'] = 'O tipo de questão {$a} ainda não é suportado pela auditoria semântica.';
$string['emptyselection'] = 'Nenhuma questão foi selecionada.';
$string['emptycategory'] = 'Não há questões acessíveis nesta categoria.';
$string['invalidairesponse'] = 'A IA retornou uma resposta inválida e os apontamentos dela foram ignorados.';
$string['privacy:metadata'] = 'O plugin Auditoria de questões não armazena dados pessoais. O conteúdo da questão é ' .
    'enviado ao local_ai_bridge apenas enquanto um usuário autorizado executa uma auditoria.';
$string['local_empty_questiontext'] = 'O enunciado da questão está vazio.';
$string['local_empty_name'] = 'O nome da questão está vazio ou não é significativo.';
$string['local_no_correct_answer'] = 'Nenhuma resposta correta foi encontrada para um tipo de questão que exige resposta correta.';
$string['local_fraction_inconsistent'] = 'As frações das respostas parecem incoerentes.';
$string['local_duplicate_answer'] = 'Duas ou mais respostas estão literalmente duplicadas.';
$string['local_empty_answer'] = 'Existe pelo menos uma resposta ou par de associação vazio.';
$string['local_feedback_missing'] = 'Não foi encontrado feedback na questão nem nas respostas.';
$string['local_html_invalid'] = 'A questão contém problemas simples de estrutura HTML.';
$string['local_structure_problem'] = 'A questão possui um problema estrutural para seu tipo.';
$string['local_unsupported'] = 'Este tipo de questão ainda não é suportado pela primeira versão da Auditoria de questões.';
$string['suggest_add_questiontext'] = 'Adicione um enunciado completo antes de utilizar a questão.';
$string['suggest_meaningful_name'] = 'Use um nome curto e descritivo que ajude o professor a identificar a questão no banco.';
$string['suggest_empty_answer'] = 'Remova a alternativa vazia ou complete seu conteúdo.';
$string['suggest_duplicate_answer'] = 'Remova a duplicata ou reescreva-a para que cada resposta represente uma opção distinta.';
$string['suggest_fraction_range'] = 'Revise a fração da resposta; no Moodle, as frações normalmente devem permanecer entre -1 e 1.';
$string['suggest_correct_answer'] = 'Defina pelo menos uma resposta com fração positiva de acerto.';
$string['suggest_single_fraction'] = 'Uma questão de múltipla escolha com resposta única normalmente deve ter exatamente ' .
    'uma alternativa com 100% de acerto.';
$string['suggest_multi_fraction'] = 'Revise as frações positivas; em questões com múltiplas respostas, normalmente elas ' .
    'somam 100%.';
$string['suggest_full_credit'] = 'Nenhuma resposta concede crédito integral. Confirme se a pontuação apenas parcial é intencional.';
$string['suggest_multichoice_structure'] = 'Questões de múltipla escolha devem conter pelo menos duas alternativas.';
$string['suggest_truefalse_structure'] = 'Questões verdadeiro/falso devem conter exatamente os dois registros de resposta ' .
    'esperados.';
$string['suggest_accepted_answer'] = 'Adicione pelo menos uma resposta aceita.';
$string['suggest_matching_pair'] = 'Complete os dois lados de cada par de associação ou remova o par incompleto.';
$string['suggest_matching_structure'] = 'Questões de associação devem conter pelo menos dois pares completos.';
$string['suggest_feedback'] = 'Considere adicionar feedback quando ele puder ajudar o estudante a compreender o resultado.';
$string['suggest_html'] = 'Revise as tags HTML de abertura e fechamento no conteúdo afetado.';
$string['bridgepermission'] = 'Você não possui permissão para utilizar o local_ai_bridge.';
$string['bridgetenant'] = 'Não há um tenant de IA habilitado para o seu usuário.';
$string['bridgeuserdisabled'] = 'Seu acesso ao bridge de IA está desabilitado.';
$string['bridgepurpose'] = 'O purpose questionaudit-review não está habilitado para o seu tenant.';
$string['bridgeroute'] = 'Não há rota de IA disponível para o purpose questionaudit-review.';
$string['bridgefailed'] = 'O bridge de IA não conseguiu concluir a análise.';
$string['deterministicsummary'] = 'As verificações determinísticas produziram {$a} apontamento(s).';
$string['emptyevidence'] = 'Não há trecho textual porque o campo afetado está vazio.';
