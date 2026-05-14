export function formatData(date) {
	if (!date) return "-";
	return new Date(date).toLocaleDateString("pt-BR");
}
