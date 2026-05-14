/** @import { MenuItem, MenuItemRefactored } from '@/Assets/GlobalTypes'; */

/** @param {MenuItem} item */
export function isMenuActive(item) {
    const cur = route().current();
    if (item.route && item.route === cur) {
        return true;
    } else if (item.items?.length > 0) {
        let childActive = false;
        for (let i = 0; i < item.items.length; i++) {
            const child = item.items[i];
            childActive = isMenuActive(child);
            if (childActive) {
                return true;
            }
        }
    }
    return false;
}

/**
 * @param {MenuItem} item
 * @returns {MenuItemRefactored}
*/
export function recursiveMenuItem(item) {
    /** @type {MenuItem} */
    const tmp = { label: item.label, icon: item.icon }
    if (item.command) {
        tmp.command = item.command;
        tmp.hasPointer = true;
    } else if (item.route || item.url) {
        const url = item.url || route(item.route)
        if (item.target) {
            tmp.command = () => window.open(url, item.target)
        } else {
            tmp.url = url;
        }
        tmp.hasPointer = true;
    }
    if (item.items?.length > 0) {
        tmp.hasPointer = true;
    }
    tmp.items = item.items?.map(recursiveMenuItem)
    return tmp;
}
