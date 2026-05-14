import { route as routeFn, RouteList } from 'ziggy-js';

export type AppEnv = 'development' | 'production' | 'local';

declare global {
    var route: typeof routeFn;
}

export type JetStreamProp = {
    canCreateTeams: boolean,
    canManageTwoFactorAuthentication: boolean,
    canUpdatePassword: boolean,
    canUpdateProfileInformation: boolean,
    hasEmailVerification: boolean,
    flash: any[],
    hasAccountDeletionFeatures: boolean,
    hasApiFeatures: boolean,
    hasTeamFeatures: boolean,
    hasTermsAndPrivacyPolicyFeature: boolean,
    managesProfilePhotos: boolean
}

export namespace Heimdall {
    export type UserProfile = {
       id: number,
       profile_id: number,
        status: 1 | 0,
        profile: Profile,
    }
    export type Profile = {
        id: number,
        name: string,
        short_name: string,
        status: 1 | 0,
    }
    export type Menu = {
        label: string,
        icon: string,
        route: keyof RouteList,
        href: string,
        target?: '_blank'|'_self'|'_parent'|'_top',
        items?: Menu[]
    }
    export type Prop = {
        token: string,
        perfis: UserProfile[],
        menus: {
            left_sidebar: Menu[],
            topnav: Menu[],
            topnav_user: Menu[],
            topnav_right: Menu[],
        },
        permissoes: {
            [key: string]: string,
        }
    }
}


export type Breadcrumb = {
    title: string;
    url: string;
}

export type Breadcrumbs = Breadcrumb[];

export type MenuItem = {
    label: string;
    icon: string;
    command: ()=>any;
    items?: MenuItem[];
    url?: string;
    route?: keyof RouteList;
    target?: '_blank'|'_self'|'_parent'|'_top';
}

export type MenuItemRefactored = Pick<MenuItem, 'label' | 'icon' | 'command' | 'url'> & {
    items?: MenuItemRefactored[];
    hasPointer?: boolean;
}

export interface PaginateLink {
    url: string
    label: string
    active: boolean
}
export interface Paginate<T = any> {
    data: T[]
    current_page: number
    first_page_url: string
    from: number
    last_page: number
    last_page_url: string
    links: PaginateLink[]
    next_page_url: string
    path: string
    per_page: number
    prev_page_url: string
    to: number
    total: number
}
export interface ApiPaginate<T = any> {
    data: T[]
    links: {
    first?: string
    last?: string
    prev?: string
    next?: string
    }
    meta: {
    current_page: number
    from: number
    last_page: number
    links: PaginateLink[]
    path: string
    per_page: number
    to: number
    total: number
    }
}
