import { useForm } from "@inertiajs/vue3";

export const formCreate = useForm({
	titulo: "",
	descricao: "",
	prioridade: 0,
});

export const formEdit = useForm({
	id: null,
	titulo: "",
	descricao: "",
	prioridade: 0,
});

export const formDelete = useForm({
	id: null,
});
