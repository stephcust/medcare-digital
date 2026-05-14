export function formatPrioridade(p) {
	switch (p) {
		case 0:
			return "Baixa";
		case 1:
			return "Média";
		case 2:
			return "Alta";
		default:
			return "-";
	}
}
