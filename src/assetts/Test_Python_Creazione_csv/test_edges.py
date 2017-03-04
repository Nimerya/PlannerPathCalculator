import random,time

attributes = ['"from"','"to"','"attr1"','"attr2"','"attr3"','"attr4"','"attr5"']
ranges = {'"attr1"': [1.3, 4.5],'"attr2"': [1.3, 4.5],'"attr3"': [1.3, 4.5],'"attr4"': [1.3, 4.5],'"attr5"': [1.3, 4.5],}
attr = []
file = open('Archi1.csv','w')


for i in range(0,len(attributes)):
    if i < len(attributes) - 1:
        file.write(attributes[i] + ',')
    elif i == len(attributes) - 1:
        file.write(attributes[i])
file.write('\n')
start = time.clock()
split_size = 2000000
depth = 1
nodes = int((split_size**(depth + 1) - 1)/(split_size - 1))
print(nodes)
print(nodes - split_size**depth)
for i in range(1,nodes - (split_size**depth) + 1):
    for j in range(- split_size + 2, 2):
        s = '"vertex_' + str(i) + '","vertex_' + str(split_size*i + j) + '",'
        for j in range(2,len(attributes)):
            s = s + '"' + str(round(random.uniform(ranges[attributes[j]][0],ranges[attributes[j]][1]),2)) + '",'
        s = s[:-1]
        file.write(s + '\n')
print(time.clock() - start)
