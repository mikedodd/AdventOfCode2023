import {init} from "z3-solver";
import fs from 'fs/promises';

interface Z3Variables {
    intercept: { x: any; y: any; z: any };
    velocity: { x: any; y: any; z: any };
}

/**
 * Creates Z3 variables for intercept and velocity.
 *
 * @param {any} context - The context object used for creating variables.
 *
 * @returns {Z3Variables} - An object containing Z3 variables for intercept and velocity.
 */
const createZ3Variables = (context: any): Z3Variables => {
    return {
        intercept: {
            x: context.Real.const("x"),
            y: context.Real.const("y"),
            z: context.Real.const("z"),
        },
        velocity: {
            x: context.Real.const("vx"),
            y: context.Real.const("vy"),
            z: context.Real.const("vz"),
        },
    };
};

/**
 * The main function is the entry point of the program.
 * It reads the content of a file, and then retrieves the starting coordinates
 * using the content of the file.
 *
 * @returns {Promise<void>} A promise that resolves when the starting coordinates
 *                          have been retrieved.
 */
const main = async () => {
    const fileContent = await fs.readFile(`./day_24/input.txt`, 'utf-8');
    await calculateProjectileInterceptionPoint(fileContent);
};

/**
 * Parses the input string and returns an array of projectile objects.
 *
 * @param {string} input - The input string containing projectile data.
 * @returns {Array} An array of projectile objects.
 */
const parseProjectiles = (input: string) => {
    return input
        .split("\n")
        .slice(0, 3)
        .map((line) => {
            const [xPos, yPos, zPos] = line.split("@")[0].split(",").map(Number);
            const [xVel, yVel, zVel] = line.split("@")[1].split(",").map(Number);
            return {xPos, yPos, zPos, xVel, yVel, zVel, velocityRatio: yVel / xVel};
        });
}

/**
 * Calculates the interception point of projectiles based on the given input.
 *
 * @param {string} input - The input string containing information about each projectile.
 * @returns {Promise<void>} - A Promise that resolves when the interception point is calculated.
 */
export const calculateProjectileInterceptionPoint = async (input: string) => {
    const projectiles = parseProjectiles(input);
    const { Context } = await init();
    const Z3 = Context("main");
    const { intercept, velocity } = createZ3Variables(Z3);
    const solver = new Z3.Solver();

    calculateInterceptorCalculations(solver, intercept, velocity, Z3, projectiles);

    const isSatisfiable = await solver.check();
    if (isSatisfiable !== "sat") return -1; // If the solver cannot find a solution, return -1

    const model = solver.model();
    const xInterceptValue = Number(model.eval(intercept.x));
    const yInterceptValue = Number(model.eval(intercept.y));
    const zInterceptValue = Number(model.eval(intercept.z));

    console.log("Interception point: " + (xInterceptValue + yInterceptValue + zInterceptValue));
};

/**
 * Calculates the interceptor calculations using the given parameters.
 *
 * @param {any} solver - The solver object used for adding constraints.
 * @param {any} intercept - The intercept object containing the intercept coordinates.
 * @param {any} velocity - The velocity object containing the velocity coordinates.
 * @param {any} Z3 - The Z3 object used for creating variables.
 * @param {any} projectiles - The array of projectiles to calculate with.
 */
function calculateInterceptorCalculations(solver: any, intercept: any, velocity: any, Z3: any, projectiles: any){
    for (let i = 0; i < projectiles.length; i++) {
        const projectile = projectiles[i];
        const tVariable = Z3.Real.const(`t${i}`);
        solver.add(tVariable.ge(0));
        solver.add(intercept.x.add(velocity.x.mul(tVariable)).eq(tVariable.mul(projectile.xVel).add(projectile.xPos)));
        solver.add(intercept.y.add(velocity.y.mul(tVariable)).eq(tVariable.mul(projectile.yVel).add(projectile.yPos)));
        solver.add(intercept.z.add(velocity.z.mul(tVariable)).eq(tVariable.mul(projectile.zVel).add(projectile.zPos)));
    }
}

main();
